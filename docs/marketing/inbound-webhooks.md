---
title: Inbound webhooks and events API
description: Ingest leads from Zapier, LinkedIn Lead Gen, and other tools, track custom in-app behavioral events, and see which lead capture endpoints need the API token.
---

Two server-to-server endpoints bring data from other systems into Focal: the external lead webhook creates or updates contacts from lead sources such as Zapier or LinkedIn Lead Gen, and the behavioral events API records what contacts do in your product. Both need the marketing API token.

Paths below use the default API prefix, `api/marketing`. See [public routes](../configuration.md#public-routes) to change it.

## Authentication

Server-to-server endpoints use the `FOCAL_MARKETING_API_TOKEN` shared secret (config key `focal-marketing.api.token`). Send it as an `Authorization: Bearer` header, an `X-Focal-Token` header, or a `token` query parameter for providers that only let you enter a URL. A missing or wrong token returns `401`. While no token is configured, these endpoints return `403`. See [API tokens](../configuration.md#api-tokens).

They're rate limited by `focal-api` (600 requests per minute per IP by default, see [rate limits](../configuration.md#rate-limits)) and are exempt from CSRF verification.

### Lead capture endpoints at a glance

| Endpoint | Route name | Token | Rate limiter | Documented in |
| --- | --- | --- | --- | --- |
| `POST /api/marketing/leads/webhook/{source}` | `focal.marketing.leads.webhook` | Yes | `focal-api` | [below](#external-lead-webhook) |
| `POST /api/marketing/events/track` | `focal.marketing.events.track` | Yes | `focal-api` | [below](#custom-behavioral-events) |
| `POST /api/marketing/workflows/{workflow}/enroll` | `focal.marketing.workflows.enroll-webhook` | Yes | `focal-api` | [Workflows](workflows.md#enrollment-webhook) |
| `POST /api/marketing/events/{slug}/attendance-webhook` | `focal.marketing.events.attendance-webhook` | Yes | `focal-api` | [Events](events-and-assets.md#attendance-webhook) |
| `POST /api/marketing/forms/{slug}` | `focal.marketing.forms.api-submit` | No | `focal-public` | [Forms](forms-and-landing-pages.md#form-api-endpoint) |
| `POST /api/marketing/events/{slug}/register` | `focal.marketing.events.register` | No | `focal-public` | [Events](events-and-assets.md#registration-endpoint) |
| `POST /marketing/track/pageview` | `focal.marketing.track.pageview` | No | `focal-public` | [Web tracking](web-tracking.md#pageview-endpoint) |
| `POST /marketing/forms/auto-capture` | `focal.marketing.forms.auto-capture` | No | `focal-public` | [Web tracking](web-tracking.md#form-auto-capture) |

All of these are CSRF exempt. Email delivery webhooks (ESP bounces and complaints) are covered in [deliverability](deliverability.md).

## External lead webhook

| Method | URI | Route name |
| --- | --- | --- |
| `POST` | `/api/marketing/leads/webhook/{source}` | `focal.marketing.leads.webhook` |

`{source}` names the lead source, for example `zapier`, `linkedin`, or `zoom`. It's stored on the contact as the `lead_source` custom property.

```bash
curl -X POST https://your-app.test/api/marketing/leads/webhook/linkedin \
  -H "Authorization: Bearer $FOCAL_MARKETING_API_TOKEN" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{
    "email": "Jordan@Globex.com",
    "first_name": "Jordan",
    "last_name": "Lee",
    "company": "Globex",
    "job_title": "Head of Sales",
    "campaign": "q3-abm",
    "properties": {"region": "EMEA"}
  }'
```

| Field | Rules |
| --- | --- |
| `email` | Required, valid email, max 255. |
| `first_name`, `last_name`, `company`, `job_title`, `campaign` | Optional strings, max 255. |
| `phone` | Optional string, max 50. |
| `properties` | Optional object, merged into the contact's custom properties. |

```json
{
    "success": true,
    "contact_id": 31,
    "is_new": true,
    "lead_score": 15,
    "enrolled_workflows": 0,
    "message": "Lead successfully ingested into Focal CRM."
}
```

The route declares `{source}` as optional, but a request without it (`/api/marketing/leads/webhook`) fails with a `500` error. Always include a source segment.

`Focal\Marketing\Actions\IngestExternalLeadAction` processes the lead in a database transaction:

1. Loads the contact by lowercased email, or creates one with `lifecycle_stage` `lead`, `lead_status` `new`, and a score of 0.
2. Overwrites `first_name`, `last_name`, `phone`, and `job_title` with any non-empty values from the payload. Unlike form submissions, existing values are replaced.
3. Sets the custom properties `lead_source` (the source) and `lead_campaign` (the `campaign` field), merges `properties` on top, and sets `last_contacted_at` to now.
4. If `company` is given, loads the company with that exact name or creates it with the email's domain as `domain`, and associates it with the contact as `primary`.
5. Applies the `form_submission` [scoring event](lead-scoring.md) (15 points by default), described as `Ingested via {source}`.
6. Enrolls the contact in every active `form_submitted` workflow. The workflows' `trigger_config.form_id` isn't checked, so every such workflow receives external leads.

`enrolled_workflows` is the number of active `form_submitted` workflows, including any the contact was already enrolled in or that have no steps. `lead_score` is the score after step 5.

You can call the action directly, for example from an import job. `email` is the only required key:

```php
use Focal\Marketing\Actions\IngestExternalLeadAction;

$result = app(IngestExternalLeadAction::class)->execute([
    'email' => 'jordan@globex.com',
    'first_name' => 'Jordan',
    'source' => 'csv-import',
    'properties' => ['region' => 'EMEA'],
]);

$result['contact'];                  // Contact
$result['is_new'];                   // bool
$result['lead_score'];               // int
$result['enrolled_workflows_count']; // int
```

## Custom behavioral events

Track what contacts do in your product (created a project, invited a teammate, hit a usage limit) to score them and trigger workflows.

| Method | URI | Route name |
| --- | --- | --- |
| `POST` | `/api/marketing/events/track` | `focal.marketing.events.track` |

```bash
curl -X POST https://your-app.test/api/marketing/events/track \
  -H "Authorization: Bearer $FOCAL_MARKETING_API_TOKEN" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"event_name": "project_created", "email": "builder@example.com", "properties": {"plan": "pro", "projects": 3}}'
```

| Field | Rules |
| --- | --- |
| `event_name` | Required string, max 255. |
| `contact_id` | Optional number. Takes precedence over `email` when the contact exists. |
| `email` | Optional valid email. Used to find the contact, or create it, when there's no `contact_id` match. |
| `properties` | Optional object, stored with the event. |

```json
{
    "success": true,
    "event_id": 1,
    "event_name": "project_created",
    "contact_id": 12,
    "company_id": null,
    "message": "Behavioral event tracked and processed successfully."
}
```

`Focal\Marketing\Actions\TrackCustomBehavioralEventAction` stores the event in `Focal\Marketing\Models\CustomBehavioralEvent` (`contact_id`, `company_id`, `event_name`, `properties`, `occurred_at`). An email without a matching contact creates one (`lead` / `new`) and runs [lead-to-account matching](lead-scoring.md#lead-to-account-matching) on it. `company_id` is the contact's first associated company.

With a contact, the action also:

1. logs a `Custom Event: {event_name}` task with the properties as JSON;
2. applies the `custom_event` [scoring event](lead-scoring.md) (5 points by default, or the score of an active `custom_event` rule) on every event;
3. enrolls the contact in active `custom_event` workflows whose `trigger_config.event_name` matches (case-insensitively) or is unset;
4. recalculates the company's [intent](lead-scoring.md#account-intent) if there's a company.

Without `contact_id` or `email`, the event is stored anonymously and nothing else happens.

From PHP, for example in an event listener in your app:

```php
use Focal\Marketing\Actions\TrackCustomBehavioralEventAction;

$event = app(TrackCustomBehavioralEventAction::class)->execute(
    eventName: 'project_created',
    contact: $contact,
    properties: ['source' => 'onboarding'],
);
```

The signature is `execute(string $eventName, ?Contact $contact = null, ?string $email = null, array $properties = [], ?CarbonInterface $occurredAt = null): CustomBehavioralEvent`. `$occurredAt` defaults to now.

The package adds a `customBehavioralEvents` relation to `Contact`. Events are also counted by the [conversion funnel](attribution.md#conversion-funnels).
