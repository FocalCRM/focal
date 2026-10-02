---
title: Email campaigns
description: Create broadcast email campaigns, choose their audience, schedule them in each recipient's time zone, run A/B tests, and track opens and clicks.
---

A campaign is a one-off email to an audience: a `Focal\Marketing\Models\Campaign` with a subject, a sender, a [template](email-templates.md), and a Core list. Dispatching it creates a `CampaignRecipient` row per contact, and those rows carry the tokens that power open tracking, click tracking, and unsubscribe links.

## Creating a campaign

```php
use Focal\Marketing\Models\Campaign;

$campaign = Campaign::create([
    'name' => 'March newsletter',
    'subject' => 'What shipped in March',
    'preview_text' => 'Three new features and a webinar invite',
    'sender_name' => 'Acme',
    'sender_email' => 'news@acme.test',
    'reply_to_email' => 'support@acme.test',
    'template_id' => $template->id,
    'crm_list_id' => $list->id,
]);
```

`name`, `subject`, `sender_name`, and `sender_email` are required by the database. The `defaults.sender_*` config values aren't applied to new campaigns; only [proofs](#sending-a-proof) fall back to them.

A new campaign is `CampaignStatus::Draft` and `CampaignType::Regular`. The other `type`, `Automated`, is a label only: nothing in the package treats it differently.

### Attributes

| Attribute | Default | Purpose |
| :--- | :--- | :--- |
| `template_id` | `null` | The [template](email-templates.md) to send. Without one, the body is `<p>{{content}}</p>` |
| `crm_list_id`, `list_id` | `null` | The audience. `crm_list_id` wins if both are set |
| `topic_id` | `null` | A [subscription topic](subscriptions-and-compliance.md#subscription-topics) the recipient must be subscribed to |
| `topic` | `null` | A topic slug matched against the contact's `marketing_topics` |
| `status` | `draft` | A `CampaignStatus` |
| `scheduled_at` | `null` | When `marketing:dispatch-scheduled` should send a `Scheduled` campaign |
| `send_by_timezone`, `send_in_recipient_timezone` | `false` | Deliver at a local time in each recipient's time zone |
| `scheduled_local_time` | `null` | Local send time as `HH:MM` |
| `recipient_send_hour` | `9` | Local send hour when `scheduled_local_time` is empty |
| `use_sto` | `false` | Send-time optimization from each contact's open history |
| `utm_auto_tag` | `true` | Add UTM parameters to links |
| `utm_campaign` | `null` | Value for `utm_campaign`; the campaign name is used when empty |
| `is_ab_test` and the `ab_*` fields | | See [A/B testing](#ab-testing) |
| `budget` | `null` | Planned spend |
| `actual_cost`, `actual_spend` | `0.00` | Actual spend, used by [attribution](attribution.md) ROI reports |
| `target_leads`, `target_pipeline`, `target_revenue` | `null` | Goals |
| `properties` | `null` | Free-form array |

### Metrics

Dispatch, tracking, and webhooks keep these counters up to date: `total_recipients`, `delivered_count`, `opens_count`, `unique_opens_count`, `clicks_count`, `unique_clicks_count`, `bounces_count`, and `unsubscribes_count`.

Four computed attributes read them:

| Attribute | Formula |
| :--- | :--- |
| `open_rate` | `unique_opens_count / delivered_count × 100`, one decimal |
| `click_rate` | `unique_clicks_count / delivered_count × 100` |
| `ctor` | `unique_clicks_count / unique_opens_count × 100` |
| `leads_progress_percentage` | `unique_clicks_count / target_leads × 100` |

```php
$campaign->open_rate; // 42.5
```

## Choosing the audience

Point the campaign at a Core list with `crm_list_id` (or `list_id`). If the list is an active list, dispatch re-evaluates its criteria first, so the audience is current at send time. See [Core concepts](../core/index.md) for lists.

```php
use Focal\Core\Enums\ListType;
use Focal\Core\Models\CrmList;

$list = CrmList::create([
    'name' => 'Engaged leads',
    'entity_type' => 'contact',
    'type' => ListType::Active,
    'criteria' => [
        ['property' => 'lead_score', 'operator' => '>=', 'value' => 50],
    ],
]);

$campaign->update(['crm_list_id' => $list->id]);
```

> **A campaign without a list goes to every contact in your CRM.** If neither `crm_list_id` nor `list_id` is set, dispatch uses `Contact::all()`.

You can also pass the contacts yourself. The list is still synced, but your collection is used instead of its members:

```php
use Focal\Core\Models\Contact;
use Focal\Marketing\Actions\DispatchCampaignAction;

$contacts = Contact::query()->where('lifecycle_stage', 'customer')->get();

app(DispatchCampaignAction::class)->execute($campaign, $contacts);
```

### Who is skipped

Dispatch skips, and counts as suppressed, any contact:

- with an empty email address
- whose address is unsubscribed or bounced in `MarketingSubscription`, or on the [suppression list](deliverability.md#the-suppression-list)
- who is unsubscribed from the campaign's `topic_id` topic
- whose `marketing_topics` doesn't include the campaign's `topic` slug (a contact whose `marketing_topics` is `null` receives every topic)
- who fails the [fatigue check](#fatigue-protection), when it's enabled

[Subscriptions and compliance](subscriptions-and-compliance.md) explains how each of these is set. Dispatch doesn't require a [double opt-in](subscriptions-and-compliance.md#double-opt-in) confirmation.

## Dispatching

```php
use Focal\Marketing\Actions\DispatchCampaignAction;

$result = app(DispatchCampaignAction::class)->execute($campaign);

// ['total_recipients' => 2, 'delivered_count' => 1, 'suppressed_count' => 1]
```

`execute(Campaign $campaign, ?Collection $explicitContacts = null): array` sets the campaign to `Sending`, then for each eligible contact:

1. Creates a `CampaignRecipient` with status `Sent`, a 40-character `tracking_token`, and a 40-character `unsubscribe_token`.
2. Compiles the message with `CompileCampaignMessageAction` (see [The compiled message](#the-compiled-message)).
3. Logs a task activity on the contact titled `Marketing Campaign: {name}`.
4. Sets the contact's `last_marketing_email_sent_at`.

When it finishes, it sets `sent_at`, `total_recipients`, and `delivered_count`. The status becomes `Sent` if every eligible contact was handled, or stays `Sending` if some are waiting for their [local send time](#local-time-and-send-time-optimization) or an [A/B test](#ab-testing) result.

Dispatch runs synchronously in the calling process, one contact at a time. It doesn't check the campaign's current status, so calling it twice creates a second set of recipients.

### Delivering the messages

Dispatch doesn't send email. It compiles each message and then discards it. To deliver campaign email, extend `CompileCampaignMessageAction` and bind your class in the container.

Every delivery path calls this class's `execute()` once per recipient: `DispatchCampaignAction`, the local-time release in `marketing:dispatch-scheduled`, and the A/B rollout in `marketing:evaluate-ab-tests`.

```php
namespace App\Marketing;

use Focal\Marketing\Actions\CompileCampaignMessageAction;
use Focal\Marketing\Models\Campaign;
use Focal\Marketing\Models\CampaignRecipient;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class SendCampaignMessage extends CompileCampaignMessageAction
{
    public function execute(Campaign $campaign, CampaignRecipient $recipient): string
    {
        $html = parent::execute($campaign, $recipient);

        $subject = $recipient->variant === 'B' && filled($campaign->variant_b_subject)
            ? $campaign->variant_b_subject
            : $campaign->subject;

        Mail::html($html, function (Message $message) use ($campaign, $recipient, $subject): void {
            $message->to($recipient->email)
                ->from($campaign->sender_email, $campaign->sender_name)
                ->subject($subject);

            if (filled($campaign->reply_to_email)) {
                $message->replyTo($campaign->reply_to_email);
            }

            $message->getHeaders()->addTextHeader('List-Unsubscribe', '<'.$recipient->getUnsubscribeUrl().'>');
        });

        return $html;
    }
}
```

```php
// app/Providers/AppServiceProvider.php
use App\Marketing\SendCampaignMessage;
use Focal\Marketing\Actions\CompileCampaignMessageAction;

public function register(): void
{
    $this->app->bind(CompileCampaignMessageAction::class, SendCampaignMessage::class);
}
```

This sends each message synchronously inside the dispatch loop. For large lists, dispatch a queued job from `execute()` instead of calling `Mail` directly. To match bounces to recipients, also pass the recipient's `tracking_token` to your provider as described in [Deliverability](deliverability.md#matching-events-to-recipients).

### Sending a proof

`SendCampaignProofAction` sends a test copy of the campaign. Unlike dispatch, it does send mail, synchronously, with your default mailer.

```php
use Focal\Marketing\Actions\SendCampaignProofAction;

$result = app(SendCampaignProofAction::class)->execute($campaign, 'me@acme.test, legal@acme.test');

// ['success' => true, 'sent_to' => ['me@acme.test', 'legal@acme.test'], 'message' => 'Proof email successfully dispatched to me@acme.test, legal@acme.test']
```

`execute(Campaign $campaign, string|array $recipientEmails, ?Contact $sampleContact = null): array` accepts a comma-separated string or an array and drops invalid addresses.

- The mailable is `Focal\Marketing\Mail\CampaignProofMailable`, and the subject is prefixed with `[TEST] `.
- Merge tags are filled from `$sampleContact`, or the first contact on the campaign's `crm_list_id` list, or the first contact in the database.
- The unsubscribe link points at a placeholder token, and no tracking is added.
- Mail errors are caught and returned as `success: false` with the exception message.

## Scheduling

To send later, set the status to `Scheduled` and a `scheduled_at`:

```php
use Focal\Marketing\Enums\CampaignStatus;

$campaign->update([
    'status' => CampaignStatus::Scheduled,
    'scheduled_at' => now()->addDay()->setTime(9, 0),
]);
```

`marketing:dispatch-scheduled` does two things each run:

1. Dispatches every `Scheduled` campaign whose `scheduled_at` is now or earlier.
2. For every `Sending` campaign with `send_by_timezone`, `send_in_recipient_timezone`, or `use_sto` on, it releases each `Pending` recipient whose `scheduled_send_at` is past or within five minutes. When a campaign has no pending recipients left, it's marked `Sent`.

Schedule it every minute; see [Installation](../installation.md#schedule-the-commands). To stop a scheduled campaign, change its status, for example to `CampaignStatus::Cancelled`.

## Local time and send-time optimization

Turn on `send_by_timezone` (or the older `send_in_recipient_timezone`) to deliver at the same local time in each recipient's time zone:

```php
$campaign->update([
    'status' => CampaignStatus::Scheduled,
    'scheduled_at' => now(),
    'send_by_timezone' => true,
    'scheduled_local_time' => '09:00',
]);
```

When the campaign is dispatched, `CalculateRecipientOptimalSendTimeAction` works out each contact's send time. Recipients whose time is more than five minutes away are stored as `Pending` with a `scheduled_send_at`, and `marketing:dispatch-scheduled` releases them later. The rest are sent straight away.

The send time is calculated like this:

1. **Time zone.** The contact's `timezone` attribute or `properties['timezone']`, if it's a valid identifier. Otherwise the contact's `country` (or `properties['country']`) is mapped to a time zone for a fixed set of codes: `US`, `USA`, `GB`, `UK`, `DE`, `FR`, `NL`, `AU`, `CA`, `JP`, `IN`, `SG`, `NZ`, and `BR`. Otherwise `app.timezone`.
2. **Hour.** With `use_sto` on, the hour (in the contact's time zone) at which the contact has opened the most campaigns, or `recipient_send_hour` if the contact has never opened one. Otherwise `scheduled_local_time` if set, then `recipient_send_hour`, then 9:00.
3. **Day.** The local calendar day of `scheduled_at`, or of now if it's empty. If that time has already passed by more than 15 minutes, the next day.

You can call the calculation yourself. It returns a UTC time:

```php
$sendAt = $campaign->calculateScheduledTimeForContact($contact);
```

Send-time optimization turns on the same local-time release, so a `use_sto` campaign also needs `marketing:dispatch-scheduled`.

## A/B testing

An A/B campaign sends two variants to a sample of the audience, waits, then sends the better one to everyone else.

```php
$campaign = Campaign::create([
    'name' => 'Spring launch',
    'subject' => 'Our spring launch',
    'variant_b_subject' => 'You asked, we built it',
    'variant_b_template_id' => $otherTemplate->id, // optional
    'sender_name' => 'Acme',
    'sender_email' => 'news@acme.test',
    'template_id' => $template->id,
    'crm_list_id' => $list->id,
    'is_ab_test' => true,
    'ab_test_sample_percentage' => 40,
    'ab_test_duration_hours' => 4,
    'ab_winning_metric' => 'click_rate',
]);
```

| Attribute | Default | Purpose |
| :--- | :--- | :--- |
| `is_ab_test` | `false` | Turn on A/B mode |
| `variant_b_subject` | `null` | Subject line for variant B |
| `variant_b_template_id` | `null` | Template for variant B. Without it, variant B uses the campaign template's own [variant B](email-templates.md#variant-b) |
| `ab_test_sample_percentage` | `20` | Share of the eligible audience in the test |
| `ab_test_duration_hours` | `4` | Hours to wait after `sent_at` before picking a winner |
| `ab_winning_metric` | `open_rate` | `open_rate` or `click_rate` |
| `ab_winner_variant` | `null` | Set to `A` or `B` once evaluated |
| `ab_test_evaluated_at` | `null` | When the winner was picked |

On dispatch, the sample is rounded to an even number of at least two and split in half: the first half gets variant A and the second variant B. Everyone else is stored as a `Pending` recipient with no variant, and the campaign stays `Sending`.

With 10 eligible contacts and a 40% sample, 2 get A, 2 get B, and 6 wait.

`marketing:evaluate-ab-tests` looks at every `Sending` A/B campaign without a winner whose `sent_at` plus `ab_test_duration_hours` has passed. For each one it runs `EvaluateAbTestWinnerAction`, which:

- compares the open or click rate of the two variants (B must be strictly higher to win, so a tie goes to A)
- marks every pending recipient as `Sent` with the winning variant, compiles their message, and logs a task on the contact
- sets `ab_winner_variant`, `ab_test_evaluated_at`, and status `Sent`

You can run the evaluation yourself at any time:

```php
use Focal\Marketing\Actions\EvaluateAbTestWinnerAction;

$result = app(EvaluateAbTestWinnerAction::class)->execute($campaign);

// ['winner' => 'B', 'metric' => 'click_rate', 'variant_a_score' => 0.0, 'variant_b_score' => 50.0, 'remaining_sent' => 6]
```

Calling it on a campaign that already has a winner, or isn't an A/B test, changes nothing.

In A/B mode, dispatch doesn't apply [fatigue protection](#fatigue-protection) or [local-time sending](#local-time-and-send-time-optimization).

### Significance

`Focal\Marketing\Services\AbTestSignificanceCalculator` runs a two-tailed two-proportion z-test if you want to report confidence alongside the winner. It isn't used by the evaluation above.

```php
use Focal\Marketing\Services\AbTestSignificanceCalculator;

$stats = AbTestSignificanceCalculator::calculate(
    sampleA: 500, conversionsA: 60,
    sampleB: 500, conversionsB: 85,
    confidenceThreshold: 0.95,
);

$stats['is_significant']; // true
$stats['winning_variant']; // 'B'
```

The result also includes `rate_a`, `rate_b`, `relative_uplift_percent`, `z_score`, `p_value`, `confidence_percent`, and a `recommendation` string.

### Subject line suggestions

`GenerateAiSubjectLinesAction::execute(string $topic, string $tone = 'engaging', ?string $audience = null)` returns `suggestions` (three strings), a `variant_b` candidate, `preview_text`, and a `rationale`. Despite its name, it fills in fixed phrase templates and doesn't call an AI model. Tones are `urgent`, `curious`, `friendly`, and `bold`; anything else uses the default set.

## Fatigue protection

Fatigue protection stops a contact from getting campaign email too often. It's off by default.

```env
MARKETING_FATIGUE_PROTECTION_ENABLED=true
MARKETING_MAX_EMAILS_7_DAYS=2
MARKETING_MIN_HOURS_BETWEEN_SENDS=24
```

When it's on, standard (non-A/B) dispatch runs `CheckFatiguePolicyAction` for each contact and skips anyone who:

- has `sunset_stage` set to `suppressed` (see [Deliverability](deliverability.md#sunset-policy))
- was sent a campaign email less than `min_hours_between_sends` hours ago, by `last_marketing_email_sent_at`
- has `max_emails_per_7_days` or more non-pending campaign recipients with `sent_at` in the last 7 days

You can check a contact yourself:

```php
use Focal\Marketing\Actions\CheckFatiguePolicyAction;

$check = app(CheckFatiguePolicyAction::class)->execute($contact);

// ['can_send' => false, 'reason' => 'Contact received marketing email 3h ago (minimum interval: 24h)', 'next_available_at' => Carbon]
```

## The compiled message

`CompileCampaignMessageAction::execute(Campaign $campaign, CampaignRecipient $recipient): string` builds the HTML for one recipient, in this order:

1. **Body.** If the template has mail builder slots, they're compiled for this recipient, so [slot visibility rules](email-templates.md#conditional-slots) are applied against the contact and their first company. Otherwise the template's `body_html` is used (or the variant B HTML for a variant B recipient).
2. **Merge tags.** These seven tags are replaced, written exactly as shown with no spaces inside the braces. Any other tag is left in the email as written. Values are HTML-escaped with `e()`, so a contact named `<b>Sam</b>` appears as that literal text rather than as markup.

   | Tag | Value |
   | :--- | :--- |
   | `{{contact.first_name}}` | First name, or `there` |
   | `{{contact.last_name}}` | Last name, or empty |
   | `{{contact.email}}` | The recipient's address |
   | `{{company.name}}` | The contact's first company, or `your organization` |
   | `{{unsubscribe_url}}` | This recipient's [unsubscribe page](subscriptions-and-compliance.md#unsubscribe-links) |
   | `{{campaign.subject}}` | The campaign subject |
   | `{{campaign.name}}` | The campaign name |

3. **Smart content.** `[smart]` blocks and `{{smart:…}}` tokens are resolved for the contact; see [Smart content](email-templates.md#smart-content).
4. **UTM parameters.** When `utm_auto_tag` is on, `utm_source=focal`, `utm_medium=email`, and `utm_campaign` (the slug of `utm_campaign`, or of the campaign name) are added to every absolute link, plus `utm_content=variant_a` or `variant_b` for A/B recipients. Parameters already in a link keep their value. `mailto:`, `tel:`, `#` and unsubscribe links are left alone.
5. **Click tracking.** Every link except `mailto:`, `tel:`, `#` and unsubscribe links is rewritten to the recipient's [click-tracking URL](#tracking-opens-and-clicks).
6. **Open pixel.** A 1×1 image pointing at the recipient's open-tracking URL is added before `</body>`, or at the end.

The UTM parameters are added to the `href` HTML-escaped (`&amp;`), and the click-tracking step then encodes that escaped value. As a result, a tracked link redirects to a URL containing literal `&amp;`, which breaks every parameter after the first. Turn `utm_auto_tag` off, or put the parameters in the template links yourself, until this is fixed.

## Tracking opens and clicks

Each recipient has three links, built from its tokens:

```php
$recipient->getTrackingPixelUrl();                       // route('focal.marketing.track.open', $token)
$recipient->getClickRedirectUrl('https://acme.test/x');  // route('focal.marketing.track.click', ['token' => ..., 'url' => ..., 'sig' => ...])
$recipient->getUnsubscribeUrl();                         // route('focal.marketing.unsubscribe.show', $unsubscribeToken)
```

**Opens.** `GET /marketing/track/open/{token}` returns a transparent GIF with no-cache headers. For a known token it calls `$recipient->recordOpen()`: status becomes `Opened`, `opened_at` is set on the first open, `opens_count` increases on every request, and `unique_opens_count` on the first.

**Clicks.** `GET /marketing/track/click/{token}?url=...&sig=...` redirects only to destinations your app signed. `sig` is an HMAC-SHA256, keyed with `app.key`, over the token and the destination URL; `getClickRedirectUrl()` adds it, and `CampaignRecipient::clickSignature($token, $url)` computes it. When the signature matches and `url` is an `http` or `https` URL, the endpoint calls `$recipient->recordClick()` for a known token in the same way (status `Clicked`, `clicked_at`, `clicks_count`, `unique_clicks_count`), then redirects to `url`. Anything else (a missing or wrong `sig`, a changed `url` or token, or another scheme) returns `404` and records nothing.

Because the signature depends on `app.key`, keep the old key in `APP_PREVIOUS_KEYS` when you rotate it: signatures are checked against the current key and each previous key, so links in emails you've already sent keep working.

Both also apply a [lead scoring](lead-scoring.md) event to the contact: `EmailOpened` or `EmailClicked`.

Things to know:

- An open recorded after a click sets the status back to `Opened`. Use `opened_at` and `clicked_at` rather than `status` to tell what a recipient did.
- Image proxies and privacy features that prefetch images record opens that the recipient didn't make.
