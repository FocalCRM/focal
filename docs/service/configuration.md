---
title: Configuration reference
description: Every focal-service config key and environment variable, the full route list, and how to replace the package routes.
---

The service module reads its settings from `config/focal-service.php`. Publish the file only if you need to change something that has no environment variable:

```bash
php artisan vendor:publish --tag=focal-service-config
```

Settings shared by all Focal modules, such as the user model and rate limits, are covered in [Configuration](../configuration.md).

## Environment variables

| Variable | Config key | Default |
| :--- | :--- | :--- |
| `FOCAL_SERVICE_ROUTES_ENABLED` | `focal-service.routes.enabled` | `true` |
| `FOCAL_SERVICE_DOMAIN` | `focal-service.routes.web.domain` and `focal-service.routes.api.domain` | `null` (any domain) |
| `FOCAL_SERVICE_PREFIX` | `focal-service.routes.web.prefix` | `''` (no prefix) |
| `FOCAL_SERVICE_API_PREFIX` | `focal-service.routes.api.prefix` | `api/service` |
| `FOCAL_SERVICE_API_TOKEN` | `focal-service.api.token` | `null` (token endpoints disabled) |

The rate limits come from Core: `FOCAL_PUBLIC_RATE_LIMIT` (default 30 per minute) and `FOCAL_API_RATE_LIMIT` (default 600 per minute). See [Rate limits](#rate-limits).

## Config keys

### Tables

```php
'tables' => [
    'tickets' => 'focal_service_tickets',
    'messages' => 'focal_service_ticket_messages',
    'sla_policies' => 'focal_service_sla_policies',
    'articles' => 'focal_service_articles',
    'canned_responses' => 'focal_service_canned_responses',
    'routing_rules' => 'focal_service_routing_rules',
],
```

The models and migrations both read these names. Change them before you run the migrations. The migrations also read `focal-core.tables.contacts` and `focal-core.tables.companies` for their foreign keys.

### Ticket defaults

```php
'defaults' => [
    'priority' => 'medium',
    'source' => 'web_portal',
    'prefix' => 'TICK',
],
```

`prefix` is used for generated ticket numbers (`TICK-2026-7WBPJ`) and for recognizing ticket numbers in [inbound email](inbound-email.md#threading-replies). Use uppercase letters: generated numbers keep the prefix as written, while inbound email looks up the matched number in uppercase.

`priority` and `source` are not read by the package. A new ticket's defaults come from the model and the database columns (`medium` and `web_portal`) and from the arguments you pass to `CreateTicketAction`.

### Routes

```php
'routes' => [
    'enabled' => (bool) env('FOCAL_SERVICE_ROUTES_ENABLED', true),

    'web' => [
        'domain' => env('FOCAL_SERVICE_DOMAIN'),
        'prefix' => env('FOCAL_SERVICE_PREFIX', ''),
        'middleware' => ['web'],
    ],

    'api' => [
        'domain' => env('FOCAL_SERVICE_DOMAIN'),
        'prefix' => env('FOCAL_SERVICE_API_PREFIX', 'api/service'),
        'middleware' => ['web'],
    ],
],
```

Each group's `domain`, `prefix`, and `middleware` are passed to `Route::group()`. Empty values are dropped. For example, `FOCAL_SERVICE_PREFIX=care` serves the help center at `/care/help`, and `FOCAL_SERVICE_DOMAIN=support.example.com` serves both groups only on that host.

Both groups use the `web` middleware group by default. The browser-facing chat and deflection endpoints and the email webhook remove Laravel's CSRF middleware themselves, so they work from other sites and servers. If you set the `api` group's middleware to `['api']`, sessions are no longer started for widget requests; nothing in the API endpoints depends on the session.

Generate links with `route()` and the route names below, so prefix and domain changes are picked up.

### API token

```php
'api' => [
    'token' => env('FOCAL_SERVICE_API_TOKEN'),
],
```

Protects the [inbound email webhook](inbound-email.md#api-token). Until it is set, that endpoint returns `403`.

## Routes

The `web` group (no prefix by default):

| Method | URI | Name | Throttle | Page |
| :--- | :--- | :--- | :--- | :--- |
| GET | `/help` | `focal.help.index` | | [Knowledge base](knowledge-base.md#the-help-center) |
| GET | `/help/{slug}` | `focal.help.show` | | |
| POST | `/help/{slug}/vote` | `focal.help.vote` | `focal-public` | |
| GET | `/support` | `focal.support.create` | | [Support portal](customer-portal.md) |
| POST | `/support` | `focal.support.store` | `focal-public` | |
| GET | `/support/tickets/{token}` | `focal.support.show` | | |
| POST | `/support/tickets/{token}/reply` | `focal.support.reply` | `focal-public` | |
| GET | `/support/rate/{token}` | `focal.support.rate` | | [CSAT](customer-portal.md#csat-surveys) |
| POST | `/support/rate/{token}` | `focal.support.submitRating` | `focal-public` | |

The `api` group (prefix `api/service` by default):

| Method | URI | Name | Throttle | CSRF | Auth |
| :--- | :--- | :--- | :--- | :--- | :--- |
| POST | `/inbound-email` | `focal.service.inbound-email` | `focal-api` | Exempt | API token |
| GET | `/knowledge/suggest` | `focal.service.knowledge.suggest` | | | |
| POST | `/knowledge/deflect` | `focal.service.knowledge.deflect` | `focal-public` | Exempt | |
| POST | `/chat/start` | `focal.service.chat.start` | `focal-public` | Exempt | |
| POST | `/chat/{token}/message` | `focal.service.chat.message` | `focal-public` | Exempt | |
| GET | `/chat/{token}/messages` | `focal.service.chat.messages` | | | |

POST routes in the `web` group keep CSRF protection; the bundled forms include the token.

## Rate limits

The throttled routes use Core's two limiters, both keyed by IP address:

- `focal-public`: `focal-core.rate_limits.public`, default 30 requests per minute.
- `focal-api`: `focal-core.rate_limits.api`, default 600 requests per minute.

Each limiter has one counter per IP shared by every route that uses it, including routes in other Focal modules. A visitor who sends chat messages, votes on articles, and submits the support form is counted once against the same 30 per minute. If many customers reach your app through one proxy address, configure trusted proxies as described in [Rate limits](../configuration.md#rate-limits).

`GET /chat/{token}/messages`, which the chat widget polls every 4 seconds, and `GET /knowledge/suggest` are not throttled.

## Using your own routes

Set `FOCAL_SERVICE_ROUTES_ENABLED=false` to stop the package from registering any routes, then register the ones you want in your app. Keep the route names: the models, notifications, actions, and bundled views generate links with them.

| Name | Used by |
| :--- | :--- |
| `focal.support.show` | `Ticket::getPortalUrl()`, customer emails, portal redirects |
| `focal.support.rate` | `Ticket::getCsatUrl()`, the resolution email |
| `focal.help.show` | `DeflectTicketAction` result URLs |
| `focal.help.index`, `focal.help.vote`, `focal.support.create`, `focal.support.store`, `focal.support.reply`, `focal.support.submitRating` | The bundled views |
| `focal.service.knowledge.suggest`, `focal.service.knowledge.deflect` | The support form's suggestion script |

The simplest starting point is the package's own `routes/web.php`. This example puts the help center behind your app's login and moves the email webhook to a different path:

```php
// routes/web.php
use Focal\Core\Http\Middleware\RequireApiToken;
use Focal\Core\Support\CsrfExemption;
use Focal\Service\Http\Controllers\HelpCenterController;
use Focal\Service\Http\Controllers\InboundEmailWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/help', [HelpCenterController::class, 'index'])->name('focal.help.index');
    Route::get('/help/{slug}', [HelpCenterController::class, 'show'])->name('focal.help.show');
    Route::post('/help/{slug}/vote', [HelpCenterController::class, 'vote'])
        ->middleware('throttle:focal-public')
        ->name('focal.help.vote');
});

Route::post('/webhooks/support-email', InboundEmailWebhookController::class)
    ->withoutMiddleware(CsrfExemption::middleware())
    ->middleware([RequireApiToken::class.':focal-service.api.token', 'throttle:focal-api'])
    ->name('focal.service.inbound-email');
```

Routes in `routes/web.php` already run the `web` middleware group. `CsrfExemption::middleware()` returns the CSRF middleware classes that exist in your Laravel version (`ValidateCsrfToken`, and `PreventRequestForgery` on Laravel 13), so the webhook is exempt on both. You still need to register the support portal routes if customers will follow the links in their emails.

## Migrations

The migrations load automatically. To edit them before running, publish them:

```bash
php artisan vendor:publish --tag=focal-service-migrations
```

They create the six tables above. They require Core's contacts and companies tables and your users table, so run them after Core's migrations.

## Demo data

`Focal\Service\Database\Seeders\ServiceDatabaseSeeder` creates sample SLA policies, articles, canned responses, routing rules, and tickets. It also creates three users (`admin@focal.test`, `alex.mercer@focal.test`, `beth.caldwell@focal.test`) with the password `password`, and attaches tickets to existing contacts and companies. Use it only in local environments.

```bash
php artisan db:seed --class="Focal\Service\Database\Seeders\ServiceDatabaseSeeder"
```
