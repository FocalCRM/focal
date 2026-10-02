---
title: Sales
description: What the focalcrm/sales package adds on top of Focal Core, and the models it provides.
---

`focalcrm/sales` is Focal's sales module. It adds pipelines, deals, products, quotes with online acceptance, forecasting and quotas, lead routing, outbound sequences, qualification playbooks, and public meeting-booking links. It builds on the contacts, companies, activities, associations, and custom properties from [Focal Core](../core/index.md).

## Installation

The package requires PHP 8.3+, Laravel 12 or 13, and `focalcrm/core`. See [Installation](../installation.md) for the full setup.

```bash
composer require focalcrm/sales
php artisan migrate
```

`Focal\Sales\SalesServiceProvider` is auto-discovered. It loads the package migrations, the `focal-sales::` views, and (unless you turn them off) the public quote and meeting routes. To customize the config file, publish it:

```bash
php artisan vendor:publish --tag=focal-sales-config
```

## What it adds to Core

- **Deals are Core records.** `Focal\Sales\Models\Deal` uses Core's `HasActivities`, `HasAssociations`, `HasCustomProperties`, `AuditsProperties`, and `BelongsToTeam` traits. You log activities on a deal, associate it with contacts and companies, and store custom properties on it the same way as on a contact.
- **New relations on Core models.** The service provider registers `deals()` on `Focal\Core\Models\Contact` and `Focal\Core\Models\Company`, and `salesSequenceEnrollments()` on `Contact`. Deal links to contacts and companies are stored in Core's associations table.
- **Activities written for you.** Many sales actions log activities on deals and contacts (stage automation tasks, quote views and signatures, routed leads, sequence emails and calls, booked meetings). These are ordinary Core `Activity` records.

## Models

All models live in `Focal\Sales\Models`. Table names come from `focal-sales.tables` (see [Configuration and routes](configuration.md)).

| Model | Default table | Purpose |
| --- | --- | --- |
| `Pipeline` | `focal_pipelines` | A named sales process with ordered stages. |
| `PipelineStage` | `focal_pipeline_stages` | A stage with a win probability, closed won/lost flags, and a rotting threshold. |
| `StageAutomation` | `focal_stage_automations` | Entry requirements and actions that run when a deal enters a stage. |
| `Deal` | `focal_deals` | An opportunity in a pipeline stage, with amount, status, and owner. |
| `DealStageHistory` | `focal_deal_stage_history` | One row per stage a deal has entered, with time spent there. |
| `DealProduct` | `focal_deal_products` | A line item on a deal. Line items drive the deal amount. |
| `Quote` | `focal_quotes` | A proposal for a deal, with a public token for online acceptance. |
| `QuoteItem` | `focal_quote_items` | A line item on a quote. |
| `SalesQuota` | `focal_sales_quotas` | A revenue target for one user over a date range. |
| `LeadRoutingRule` | `focal_sales_lead_routing_rules` | Assigns owners to contacts and deals. |
| `SalesEmailTemplate` | `focal_sales_email_templates` | Email subject and body with merge tags. |
| `SalesSequence` | `focal_sales_sequences` | A multi-step outbound cadence. |
| `SalesSequenceEnrollment` | `focal_sales_sequence_enrollments` | A contact's progress through a sequence. |
| `SalesPlaybook` | `focal_sales_playbooks` | A qualification script whose answers are saved as custom properties. |
| `SalesMeetingLink` | `focal_sales_meeting_links` | A public booking page for a user. |

## Pages in this section

- [Pipelines and stages](pipelines-and-stages.md): stages, probabilities, and stage entry requirements.
- [Deals](deals.md): creating deals, moving stages, won and lost, stage history, products, and events.
- [Deal health, forecasts, and quotas](health-and-forecasting.md): rotting deals, health scores, pipeline forecasts, stage velocity, and quota attainment.
- [Quotes](quotes.md): generating quotes, totals, the public acceptance page, and expiry.
- [Lead routing](lead-routing.md): round robin and quota-weighted owner assignment.
- [Sequences, templates, and playbooks](sequences.md): outbound cadences, email merge tags, and qualification playbooks.
- [Meeting links](meeting-links.md): public booking pages for reps.
- [Configuration and routes](configuration.md): config keys, public routes, Artisan commands, scheduling, and events.

## Demo data

The package ships `Focal\Sales\Database\Seeders\SalesDatabaseSeeder`, which creates sample users, playbooks, templates, sequences, meeting links, routing rules, and deals. It creates users such as `admin@focal.test` with the password `password`, so only run it in local or demo environments:

```bash
php artisan db:seed --class="Focal\Sales\Database\Seeders\SalesDatabaseSeeder"
```
