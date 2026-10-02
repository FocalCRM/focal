---
title: Subscriptions and compliance
description: How unsubscribe links, the preference center, subscription topics, and double opt-in work, and how campaigns respect them.
---

Focal tracks consent per email address, in three layers:

- a global subscription status
- per-topic preferences that people manage in a hosted preference center
- an optional double opt-in confirmation

Campaign dispatch checks the first two before it creates a recipient. The [suppression list](deliverability.md#the-suppression-list) adds a fourth check for addresses that bounced or complained.

## Global subscription status

`Focal\Marketing\Models\MarketingSubscription` holds one row per email address (unique), with a `status` (`SubscriptionStatus::Subscribed`, `Unsubscribed`, or `Bounced`), `unsubscribed_at`, and an optional `contact_id`. A contact's row is available as `$contact->marketingSubscription`.

```php
use Focal\Marketing\Models\MarketingSubscription;

MarketingSubscription::unsubscribe('pat@example.com', $contact->id);

MarketingSubscription::isSuppressed('pat@example.com');                     // true
MarketingSubscription::isSuppressed('pat@example.com', 'product_updates');  // also checks one topic
```

`isSuppressed(string $email, int|string|null $topicIdOrSlug = null): bool` returns `true` when:

- the address's status is `Unsubscribed` or `Bounced`, or
- the address is on the [suppression list](deliverability.md#the-suppression-list), or
- a topic is given and the address isn't [subscribed to it](#subscription-topics)

Addresses are lowercased and trimmed everywhere. An address with no row counts as subscribed.

There's no helper to subscribe someone again. To do it, set the row's status yourself, and remove the address from the suppression list if it's there:

```php
use Focal\Marketing\Enums\SubscriptionStatus;
use Focal\Marketing\Models\EmailSuppression;

MarketingSubscription::query()
    ->where('email', 'pat@example.com')
    ->update(['status' => SubscriptionStatus::Subscribed, 'unsubscribed_at' => null]);

EmailSuppression::remove('pat@example.com');
```

## Unsubscribe links

Every campaign recipient gets its own unsubscribe link. Put `{{unsubscribe_url}}` in your template (the mail builder `footer` slot already does); campaigns replace it with:

```php
$recipient->getUnsubscribeUrl(); // route('focal.marketing.unsubscribe.show', $recipient->unsubscribe_token)
```

- `GET /marketing/unsubscribe/{token}` shows a confirmation page with a button. An unknown token returns `404`.
- `POST /marketing/unsubscribe/{token}` processes it:
  1. Unsubscribes the address globally with `MarketingSubscription::unsubscribe()`.
  2. Sets the recipient's status to `Unsubscribed` and increments the campaign's `unsubscribes_count` (only once per recipient).
  3. Applies an `Unsubscribed` [lead scoring](lead-scoring.md) event to the contact.
  4. Shows a confirmation page.

The link unsubscribes from all marketing email, not from one topic. To let people choose, link to the [preference center](#preference-center) as well.

### One-click unsubscribe

Gmail and Yahoo expect bulk senders to support one-click unsubscribe (RFC 8058): a `List-Unsubscribe` header plus a `List-Unsubscribe-Post: List-Unsubscribe=One-Click` header, so that the mailbox provider can POST to the URL.

The unsubscribe `POST` route is in the `web` group and keeps CSRF verification, so a provider's POST is rejected with `419`. If you send these headers, exempt the route in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->validateCsrfTokens(except: ['marketing/unsubscribe/*']);
})
```

Add your `FOCAL_MARKETING_PREFIX` to the pattern if you set one. Campaign dispatch doesn't add these headers itself; see [Delivering the messages](campaigns.md#delivering-the-messages).

## Preference center

The preference center lets a contact choose topics or opt out of everything.

```php
$url = $contact->getPreferenceCenterUrl();
// e.g. https://example.com/marketing/preferences/{token}
```

`Contact::getPreferenceCenterUrl()` (from Core) generates the contact's `marketing_verification_token` the first time it's called and saves it quietly. The preference routes accept that token or any `CampaignRecipient` unsubscribe token for the contact.

**`GET /marketing/preferences/{token}`** lists every `MarketingSubscriptionTopic` with the contact's current choices.

> If no topics exist yet, the first visit creates four: `product_updates`, `newsletter`, `webinars`, and `security`. Create your own topics before you send preference links if you don't want these.

An unknown token still renders the page, with no contact and no saved choices.

**`POST /marketing/preferences/{token}`** saves the form and redirects back with a `success` flash message. An unknown token returns `404`.

| Field | Effect |
| :--- | :--- |
| `topics[]` | Topic slugs or ids to stay subscribed to. Every topic not listed is unsubscribed |
| `opt_out_all` | When truthy, unsubscribes the address globally and sets the contact's `marketing_topics` to `[]`; `topics` is ignored |

When saving topics, the controller writes a `MarketingContactTopic` row for every topic and sets the contact's `marketing_topics` to the submitted values.

### Customizing the pages

The pages are Blade views in the `focal-marketing` namespace. The package doesn't publish them, but Laravel loads your copy first if you create it under `resources/views/vendor/focal-marketing/`:

| View | Page |
| :--- | :--- |
| `unsubscribe/show.blade.php` | Unsubscribe confirmation (receives `$recipient`) |
| `unsubscribe/confirmed.blade.php` | After unsubscribing (receives `$email`) |
| `preferences.blade.php` | Preference center (receives `$contact`, `$topics`, `$currentTopics`, `$token`, `$isSuppressed`) |
| `confirmed.blade.php` | Double opt-in confirmation (receives `$contact`) |

Keep the form actions pointed at the `focal.marketing.unsubscribe.process` and `focal.marketing.preferences.update` routes, and include `@csrf`.

## Subscription topics

Topics let people opt out of one kind of email, such as webinar invitations, without leaving your list. There are two independent mechanisms.

### Topic records

`MarketingSubscriptionTopic` has a `name`, a unique `slug`, a `description`, `is_default` (default `true`), and a `sort_order`. Each address's choice is stored in `MarketingContactTopic` (`email`, `topic_id`, `is_subscribed`, `unsubscribed_at`, `contact_id`).

```php
use Focal\Marketing\Models\MarketingSubscriptionTopic;

$webinars = MarketingSubscriptionTopic::create([
    'name' => 'Webinars',
    'slug' => 'webinars',
    'description' => 'Invitations to live sessions',
    'is_default' => false,
    'sort_order' => 2,
]);

MarketingSubscriptionTopic::setSubscription('pat@example.com', $webinars->id, true, $contact->id);

MarketingSubscriptionTopic::isSubscribed('pat@example.com', 'webinars'); // true
```

`isSubscribed(string $email, int|string $topicIdOrSlug): bool` returns the address's saved choice, or the topic's `is_default` if there isn't one. An unknown topic returns `true`.

To scope a campaign to a topic, set its `topic_id`. Dispatch then skips anyone not subscribed to that topic:

```php
$campaign->update(['topic_id' => $webinars->id]);
```

### Contact topic slugs

The contact's own `marketing_topics` column holds an array of topic slugs. `Contact::isSubscribedToTopic(string $topic)` returns `true` if the slug is in the array, or if the column is `null` (a contact who never chose is subscribed to everything).

Set a campaign's `topic` (a string, up to 50 characters) to make dispatch check it:

```php
$campaign->update(['topic' => 'product_updates']);
```

The preference center writes both mechanisms, so they agree for contacts who have used it. If you set `marketing_topics` or `MarketingContactTopic` rows yourself, keep them consistent, or scope campaigns with only one of `topic_id` and `topic`.

## Double opt-in

Double opt-in asks a new subscriber to confirm their address by clicking a link. Focal provides the confirmation endpoint; sending the email is up to you.

```php
use Illuminate\Support\Facades\Mail;

$contact->getPreferenceCenterUrl(); // ensures the contact has a marketing_verification_token

$confirmUrl = route('focal.marketing.confirm', $contact->marketing_verification_token);

Mail::raw("Confirm your subscription: {$confirmUrl}", function ($message) use ($contact): void {
    $message->to($contact->email)->subject('Please confirm your email');
});
```

`GET /marketing/confirm/{token}` finds the contact by `marketing_verification_token` (`404` if there's none), sets `marketing_email_verified_at` if it's empty, and shows the `confirmed` page. The first confirmation also applies a `PropertyMatch` [lead scoring](lead-scoring.md) event.

Things to know:

- The confirmation token is the same token the preference center uses, so anyone with a preference link can also confirm the address.
- Campaign dispatch doesn't check `marketing_email_verified_at`. To mail only confirmed contacts, pass them in yourself:

```php
use Focal\Core\Models\Contact;
use Focal\Marketing\Actions\DispatchCampaignAction;

$confirmed = $list->contacts()->whereNotNull('marketing_email_verified_at')->get();

app(DispatchCampaignAction::class)->execute($campaign, $confirmed);
```

## What the transactional API checks

The [transactional API](transactional-email.md) sends to whatever address you give it. It doesn't check subscription status, topics, or the suppression list, because transactional email (receipts, password resets) usually has to go out regardless. Check `MarketingSubscription::isSuppressed()` yourself before calling it for anything promotional.
