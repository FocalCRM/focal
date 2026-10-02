---
title: Configuration and routes
description: Reference for the focal-sales config file, public routes, Artisan commands, scheduling, views, and events.
---

This page lists everything you can configure in `focalcrm/sales`, plus the routes, commands, and events it registers. For settings shared by every Focal package, such as the user model and Core's rate limits, see [Configuration](../configuration.md).

## Publishing

```bash
php artisan vendor:publish --tag=focal-sales-config      # config/focal-sales.php
php artisan vendor:publish --tag=focal-sales-migrations  # copies migrations into database/migrations
```

Migrations are loaded from the package automatically, so publishing them is only needed if you want to edit them. Published copies keep their filenames, so Laravel treats them as the same migrations and doesn't run them twice.

## Config reference

### `tables`

Table names for every sales model. Change them before running the migrations.

| Key | Default |
| --- | --- |
| `focal-sales.tables.pipelines` | `focal_pipelines` |
| `focal-sales.tables.stages` | `focal_pipeline_stages` |
| `focal-sales.tables.deals` | `focal_deals` |
| `focal-sales.tables.stage_history` | `focal_deal_stage_history` |
| `focal-sales.tables.products` | `focal_deal_products` |
| `focal-sales.tables.quotes` | `focal_quotes` |
| `focal-sales.tables.quote_items` | `focal_quote_items` |
| `focal-sales.tables.automations` | `focal_stage_automations` |
| `focal-sales.tables.quotas` | `focal_sales_quotas` |
| `focal-sales.tables.email_templates` | `focal_sales_email_templates` |
| `focal-sales.tables.sequences` | `focal_sales_sequences` |
| `focal-sales.tables.sequence_enrollments` | `focal_sales_sequence_enrollments` |
| `focal-sales.tables.playbooks` | `focal_sales_playbooks` |
| `focal-sales.tables.meeting_links` | `focal_sales_meeting_links` |
| `focal-sales.tables.lead_routing_rules` | `focal_sales_lead_routing_rules` |

### `default_currency`

```php
'default_currency' => env('FOCAL_DEFAULT_CURRENCY', 'USD'),
```

The package does not currently read this value. New deals, quotas, and quotes take their currency from the database column default (`USD`) or from what you pass. Generated quotes copy the deal's currency.

### `routes`

```php
'routes' => [
    'enabled' => (bool) env('FOCAL_SALES_ROUTES_ENABLED', true),

    'web' => [
        'domain' => env('FOCAL_SALES_DOMAIN'),
        'prefix' => env('FOCAL_SALES_PREFIX', ''),
        'middleware' => ['web'],
    ],
],
```

| Key | Env | Default | Effect |
| --- | --- | --- | --- |
| `routes.enabled` | `FOCAL_SALES_ROUTES_ENABLED` | `true` | Set to `false` to skip registering the public routes. |
| `routes.web.domain` | `FOCAL_SALES_DOMAIN` | `null` | Serve the routes on one domain, for example `deals.example.com`. |
| `routes.web.prefix` | `FOCAL_SALES_PREFIX` | `''` | URL prefix, for example `sales` gives `/sales/quotes/{token}`. |
| `routes.web.middleware` | | `['web']` | Middleware for the route group. Keep `web`: the forms need sessions and CSRF protection. |

Empty values are dropped, so a `null` domain or empty prefix applies no constraint.

```env
FOCAL_SALES_DOMAIN=proposals.example.com
FOCAL_SALES_PREFIX=
```

## Public routes

These routes have no authentication; access is by quote token or meeting slug.

| Method | URI | Name | Purpose |
| --- | --- | --- | --- |
| GET | `/quotes/{token}` | `focal.quotes.show` | Customer quote page. See [Quotes](quotes.md#sharing-the-quote). |
| POST | `/quotes/{token}/accept` | `focal.quotes.accept` | Accept and sign a quote. |
| GET | `/meet/{slug}` | `focal.meetings.show` | Meeting booking page. See [Meeting links](meeting-links.md). |
| POST | `/meet/{slug}/book` | `focal.meetings.book` | Book a meeting. |

The two POST routes use the `throttle:focal-public` middleware. Core defines that limiter: `focal-core.rate_limits.public` requests per minute per IP, default `30` (`FOCAL_PUBLIC_RATE_LIMIT`).

If you set `routes.enabled` to `false` and register your own routes, keep the four route names. The package's views and controllers generate URLs and redirects with them.

## Views

Views are registered under the `focal-sales` namespace:

| View | Used by |
| --- | --- |
| `focal-sales::quotes.public-portal` | `focal.quotes.show` |
| `focal-sales::meetings.book` | `focal.meetings.show` |
| `focal-sales::deals.health-score-modal` | Not used by a route. Takes a `$health` array and requires Filament. See [Health score](health-and-forecasting.md#health-score). |

There is no publish tag for views. To override one, create a file with the same path under `resources/views/vendor/focal-sales/` in your app.

## Artisan commands

| Command | What it does |
| --- | --- |
| `sales:process-cadences` | Runs due sequence steps and prints a summary table. See [Processing due steps](sequences.md#processing-due-steps). |
| `sales:expire-quotes` | Sets `draft`, `sent`, and `approved` quotes whose `expires_at` is before today to `expired`. See [Expiring stale quotes](quotes.md#expiring-stale-quotes). |

Neither command takes arguments or options.

## Scheduling

The package does not add anything to the scheduler. Sequences don't advance and quotes don't expire until you schedule the commands, for example in `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('sales:process-cadences')->hourly();
Schedule::command('sales:expire-quotes')->daily();
```

Sequence steps are due by date, so running `sales:process-cadences` more than once a day only matters for picking up manual steps that reps have completed.

## Events

| Event | Dispatched when |
| --- | --- |
| `Focal\Sales\Events\DealMovedStage` | A deal changes stage. |
| `Focal\Sales\Events\DealWon` | A deal enters a closed won stage. |
| `Focal\Sales\Events\DealLost` | A deal enters a closed lost stage. |

Properties and timing are described in [Deals](deals.md#events). The package registers no listeners for them.

## Side effects at a glance

Nothing in the package is queued and nothing sends mail. These are the writes that happen in the background of a call:

| Trigger | Side effect |
| --- | --- |
| Deal enters a stage | Stage automations run; stage history row written; events dispatched; on won/lost, associated contacts' active sequence enrollments are unenrolled. |
| `DealProduct` saved, deleted, or restored | Deal `amount` recalculated. |
| `QuoteItem` saved or deleted | Quote `subtotal` and `total_amount` recalculated. |
| Quote page viewed | Draft quote becomes `sent`; a view note is logged on the deal (at most every two hours). |
| Quote accepted | Deal moved to closed won, `amount` set from the quote, note and owner task logged. |
| Lead routed | `owner_id` updated; note logged. |
| Contact enrolled in a sequence | `New` lead status becomes `InProgress`. |
| Meeting booked | Contact created or matched; meeting activity logged; active sequence enrollments unenrolled. |
