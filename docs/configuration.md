---
title: Configuration
description: Settings shared by every Focal module, including the user model, public routes, API tokens, and rate limits.
---

Each package has its own config file (`focal-core`, `focal-sales`, `focal-service`, `focal-marketing`), published with `php artisan vendor:publish --tag=focal-<package>-config`. This page covers the settings that work the same way across all of them. Module-specific options are documented in each module's section.

## The user model

Focal links records to people on your team: deal owners, ticket assignees, activity authors. It uses your application's user model for this, resolved in this order:

1. `focal-core.user_model`, set with the `FOCAL_USER_MODEL` environment variable
2. Your default auth provider's model (`auth.providers.users.model`)

```env
FOCAL_USER_MODEL=App\Models\Admin
```

The model only needs to be an Eloquent model. Where Focal shows a person's name, it reads the model's `name` attribute.

## Public routes

Sales, Service, and Marketing register routes for the things your customers use directly: quote acceptance and booking pages, the support portal and help center, hosted forms, landing pages, and tracking links. Each module has a `routes` block you can adjust:

```php
// config/focal-marketing.php
'routes' => [
    'enabled' => (bool) env('FOCAL_MARKETING_ROUTES_ENABLED', true),

    // Pages people visit: forms, landing pages, tracking and unsubscribe links.
    'web' => [
        'domain' => env('FOCAL_MARKETING_DOMAIN'),
        'prefix' => env('FOCAL_MARKETING_PREFIX', ''),
        'middleware' => ['web'],
    ],

    // Endpoints called by scripts and other servers.
    'api' => [
        'domain' => env('FOCAL_MARKETING_DOMAIN'),
        'prefix' => env('FOCAL_MARKETING_API_PREFIX', 'api/marketing'),
        'middleware' => ['web'],
    ],
],
```

| Module | Environment variables | Default `web` prefix | Default `api` prefix |
| :--- | :--- | :--- | :--- |
| Sales | `FOCAL_SALES_ROUTES_ENABLED`, `FOCAL_SALES_DOMAIN`, `FOCAL_SALES_PREFIX` | none | (no API group) |
| Service | `FOCAL_SERVICE_ROUTES_ENABLED`, `FOCAL_SERVICE_DOMAIN`, `FOCAL_SERVICE_PREFIX`, `FOCAL_SERVICE_API_PREFIX` | none | `api/service` |
| Marketing | `FOCAL_MARKETING_ROUTES_ENABLED`, `FOCAL_MARKETING_DOMAIN`, `FOCAL_MARKETING_PREFIX`, `FOCAL_MARKETING_API_PREFIX` | none | `api/marketing` |

- Set `enabled` to `false` to register none of a module's routes, for example if you build your own pages on top of its actions.
- Use `domain` to serve a module's pages from a subdomain, such as `help.example.com` for Service.
- Use `prefix` to move them under a path, such as `crm`, if they clash with your own routes.

Route names stay the same whatever the prefix or domain (for example `focal.marketing.forms.show`), so generate links with `route()` rather than hard-coding paths.

Endpoints that other sites or servers post to, such as embedded forms, the chat widget, tracking, and webhooks, are exempt from CSRF verification. Everything else uses the middleware you configure.

## API tokens

Server-to-server endpoints, such as inbound email webhooks, deliverability webhooks, lead ingestion, and the transactional email API, are protected by a shared token per module:

```env
FOCAL_MARKETING_API_TOKEN=a-long-random-string
FOCAL_SERVICE_API_TOKEN=another-long-random-string
```

These endpoints fail closed: until a token is configured they respond with `403`. A request with a missing or wrong token gets `401`. The token can be sent in any of these ways, so you can use whichever the calling service supports:

```bash
# Bearer token
curl -X POST https://example.com/api/marketing/templates/welcome/send \
  -H "Authorization: Bearer $FOCAL_MARKETING_API_TOKEN" -H "Accept: application/json" ...

# Custom header
curl ... -H "X-Focal-Token: $FOCAL_MARKETING_API_TOKEN"

# Query string, for providers that only accept a webhook URL
https://example.com/api/marketing/webhooks/deliverability?token=...
```

Prefer a header: query strings can end up in access logs. Each module's pages say which of its endpoints need the token.

## Rate limits

Focal registers two rate limiters, keyed by IP address, and applies them to its public routes:

| Limiter | Applies to | Default (requests per minute) | Environment variable |
| :--- | :--- | :--- | :--- |
| `focal-public` | Browser-facing submissions: forms, chat messages, portal replies, votes | 30 | `FOCAL_PUBLIC_RATE_LIMIT` |
| `focal-api` | Token-authenticated webhooks and sending APIs | 600 | `FOCAL_API_RATE_LIMIT` |

Both live in `focal-core.rate_limits`. If your application runs behind a load balancer or CDN, configure [trusted proxies](https://laravel.com/docs/requests#configuring-trusted-proxies) so the limits apply per visitor rather than to the proxy's address.

## Core settings

Other options in `config/focal-core.php`:

| Key | Default | What it does |
| :--- | :--- | :--- |
| `tables` | `focal_*` names | Table names for Core's models |
| `auto_associate_companies` | `false` | Associate new contacts with a company that matches their email domain |
| `freemail_domains` | `[]` | Extra consumer email domains to never treat as a company domain |
| `lifecycle.strict_transitions` | `false` | Only allow contacts to move forward through lifecycle stages |
| `enrichment.driver` | `heuristic` (`FOCAL_ENRICHMENT_DRIVER`) | Driver for company enrichment |
| `enrichment.auto_enrich` | `false` | Enrich companies automatically |

See [Core concepts](core/index.md) for how these behave.
