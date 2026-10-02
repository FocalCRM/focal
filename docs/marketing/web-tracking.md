---
title: Web tracking
description: Record pageviews with the focal.js client script, group them into visitor sessions, stitch anonymous sessions to contacts, and capture leads from existing website forms.
---

The marketing package includes first-party web tracking. A small script, `focal.js`, reports each pageview to your app and captures submissions of forms on your site. Pageviews are grouped into visitor sessions keyed by a visitor token, and when a visitor identifies themselves (by submitting a form, for example) their earlier anonymous sessions are linked to their contact record.

Paths below use the default route prefixes. See [public routes](../configuration.md#public-routes) to change them.

## The client script

| Method | URI | Route name |
| --- | --- | --- |
| `GET` | `/marketing/focal.js` | `focal.marketing.track.script` |

Add it to every page you want to track:

```html
<script src="https://your-app.test/marketing/focal.js" async></script>
```

The script is served with `Cache-Control: public, max-age=86400` and contains absolute URLs for the two endpoints below, so it honors your route prefix and domain. Once the page has loaded it:

1. Sends a pageview with `window.location.href`, the path, `document.title`, `document.referrer`, and the `utm_source`, `utm_medium` and `utm_campaign` query parameters.
2. Attaches a submit listener to every `<form>` on the page for [form auto-capture](#form-auto-capture).

Hosted [landing pages](forms-and-landing-pages.md#landing-pages) include the script automatically.

### Visitor tokens and cross-domain tracking

The pageview response sets a `focal_vid` cookie (one year) holding the visitor token. It's a regular Laravel cookie: encrypted by the `web` middleware group and HttpOnly. The script can't read it, so it relies on the browser sending the cookie back with the next request.

That works when the script and the tracked pages share your app's origin. The script calls `fetch()` without `credentials: 'include'`, so on a different domain (a marketing site on another host, for example) the cookie is neither stored nor sent, and every pageview starts a new visitor session. If you need cross-domain sessions, call the [pageview endpoint](#pageview-endpoint) yourself and pass the `visitor_token` from the previous response.

## Pageview endpoint

| Method | URI | Route name | Auth |
| --- | --- | --- | --- |
| `POST` | `/marketing/track/pageview` | `focal.marketing.track.pageview` | Public. CSRF exempt, rate limited by `focal-public`. |

```bash
curl -X POST https://your-app.test/marketing/track/pageview \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"url": "https://www.example.com/pricing?plan=pro", "path": "/pricing", "title": "Pricing", "referer": "https://google.com", "utm_source": "google"}'
```

```json
{
    "status": "success",
    "session_id": 12,
    "visitor_token": "0yE6bFQ1x2...40 characters"
}
```

Accepted fields: `visitor_token`, `url`, `path`, `title`, `referer`, `utm_source`, `utm_medium`, `utm_campaign`, and `duration_seconds`. None is validated. If `visitor_token` is missing, the `focal_vid` cookie is used, and if that's missing too a new random 40-character token is generated. `url` defaults to the `Referer` header, then `app.url`. `path` defaults to the path of `url`.

The `focal-public` limit is per IP address and defaults to 30 requests per minute (see [rate limits](../configuration.md#rate-limits)). A busy visitor who opens many pages quickly can hit it, so raise `FOCAL_PUBLIC_RATE_LIMIT` if you track high-traffic pages.

## Sessions and page views

Two models store the data:

- `Focal\Marketing\Models\VisitorSession`: one row per visitor token, with `contact_id` (once identified), `ip_address`, `user_agent`, `referer`, `utm_source`, `utm_medium`, `utm_campaign`, `first_seen_at`, and `last_seen_at`. The attribution fields are taken from the first request only. Relations: `contact`, `pageViews`.
- `Focal\Marketing\Models\PageView`: one row per pageview, with `session_id`, `contact_id`, `url`, `path`, `title`, `duration_seconds`, and `created_at` (no `updated_at`). Relations: `session`, `contact`.

```php
use Focal\Marketing\Models\VisitorSession;

$sessions = VisitorSession::query()
    ->where('contact_id', $contact->id)
    ->with('pageViews')
    ->get();
```

Each pageview also updates the session's `last_seen_at`.

### High-intent pages

When the session belongs to a known contact, visiting a path that starts with one of these prefixes applies the `property_match` [lead scoring](lead-scoring.md) event:

| Path prefix | Points in the log description |
| --- | --- |
| `/pricing` | 20 |
| `/demo` | 15 |
| `/enterprise` | 25 |
| `/quote` | 20 |

The prefixes are hard-coded in `RecordWebVisitAction`. The log entry is described as `High Intent Web Visit: {path} (+{n} pts)`, but the points actually added are those of the `property_match` event: 20 by default, or the `score_change` of an active `property_match` [scoring rule](lead-scoring.md#scoring-rules). Every visit to such a page scores again; there's no once-per-visitor limit.

### Recording visits from PHP

`Focal\Marketing\Actions\RecordWebVisitAction` does the work behind the endpoint. Call it to record server-side visits, for example from a middleware in your own app:

```php
use Focal\Marketing\Actions\RecordWebVisitAction;

$result = app(RecordWebVisitAction::class)->execute([
    'visitor_token' => 'server-side-visitor-1',
    'contact_id' => $contact->id,
    'url' => 'https://app.example.com/enterprise',
]);

$result['session'];    // VisitorSession
$result['page_view'];  // PageView, with path "/enterprise"
```

`url` is required. All other keys are optional: `visitor_token`, `contact_id`, `path`, `title`, `ip_address`, `user_agent`, `referer`, `utm_source`, `utm_medium`, `utm_campaign`, `duration_seconds`. A `contact_id` is set on the session only if the session doesn't have one yet. The HTTP endpoint never passes a `contact_id`; sessions are linked to contacts through stitching.

## Identity stitching

`Focal\Marketing\Actions\StitchVisitorToContactAction` links every session with a visitor token to a contact, and sets the contact on that session's page views that don't have one:

```php
use Focal\Marketing\Actions\StitchVisitorToContactAction;

$stitched = app(StitchVisitorToContactAction::class)->execute($visitorToken, $contact); // number of sessions updated
```

It runs automatically when a [form submission](forms-and-landing-pages.md#what-happens-on-submission) that resolves a contact by email includes a `visitor_token` field. Landing page submissions add the visitor's `focal_vid` cookie as `visitor_token` for you. For your own forms or the API endpoint, pass the token from the pageview response.

Stitching overwrites the `contact_id` of sessions that were already linked to another contact.

## Form auto-capture

`focal.js` listens for the submission of every `<form>` on the page. On submit, if the form has an input of `type="email"` or with `email` in its name, the script collects:

- `email`
- `name` from an input named `name` or containing `full_name`
- `first_name` and `last_name` from inputs whose names contain `first` and `last`
- `phone` from an input of type `tel` or with `phone` in its name
- `page_url` and the `utm_*` query parameters

and sends them to the auto-capture endpoint. The form's own submission isn't affected.

| Method | URI | Route name | Auth |
| --- | --- | --- | --- |
| `POST` | `/marketing/forms/auto-capture` | `focal.marketing.forms.auto-capture` | Public. CSRF exempt, rate limited by `focal-public`. |

```bash
curl -X POST https://your-app.test/marketing/forms/auto-capture \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email": "lin@example.com", "name": "Lin Chen", "page_url": "https://www.example.com/contact"}'
```

```json
{
    "status": "success",
    "contact_id": 31,
    "is_new": true
}
```

A missing or invalid email returns `422` with `{"status": "error", "message": "Valid email address is required."}`.

The endpoint lowercases the email and loads or creates the contact. It fills `first_name`, `last_name` and `phone` only where they're empty, splitting `name` on the first space when no `first_name` is given. Scoring works differently from the rest of the package: the endpoint writes `lead_score` directly, without a lead score log entry or lifecycle qualification.

- A new contact gets `lifecycle_stage` `marketing_qualified_lead` and a `lead_score` of 15.
- An existing contact gets 10 points added.

If a `visitor_token` is given (or the request carries a `focal_vid` cookie), sessions with that token that aren't linked yet are linked to the contact. Their page views aren't updated. A `Website Form Auto-Captured` task activity is logged on the contact.

Auto-capture doesn't create a `FormSubmission`, store custom fields, or trigger workflows. Use a [Focal form](forms-and-landing-pages.md) when you need those.

> In browsers that support `navigator.sendBeacon`, which is all current browsers, the script sends the payload with `sendBeacon()`. That sends the JSON string with a `text/plain` content type, which Laravel doesn't parse as JSON, so the endpoint answers `422` and nothing is captured. The `fetch()` fallback (used only when `sendBeacon` is unavailable) sends `application/json` and works. Until this is fixed, post to the endpoint yourself from your form handler.
