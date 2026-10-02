---
title: Introduction
description: What Focal is, how its packages fit together, and where to start.
---

Focal is an open-source CRM for Laravel, delivered as Composer packages. Instead of running a separate CRM and syncing data into it, you install the modules you need into your own application: they add models, actions, events, and routes, and they store everything in your database.

## The packages

| Package | What it adds |
| :--- | :--- |
| [`focalcrm/core`](core/index.md) | Contacts, companies, custom properties, associations, activities, lists, and the shared plumbing every module uses |
| [`focalcrm/sales`](sales/index.md) | Pipelines, deals, products, quotes, sequences, forecasting, and booking links |
| [`focalcrm/service`](service/index.md) | Tickets, SLAs, routing, a knowledge base, a customer portal, and a chat widget |
| [`focalcrm/marketing`](marketing/index.md) | Email campaigns, forms, landing pages, web tracking, lead scoring, workflows, and attribution |
| [`focalcrm/filament`](filament/index.md) | A Filament admin for every module you have installed |

Every module requires Core, and Composer installs it for you. The modules don't depend on each other, so you can install Sales without Marketing, or Service on its own.

## Two ways to use Focal

**Headless.** Use the models and actions from your own code and build whatever interface fits your product. Each module's actions are plain classes you resolve from the container, so they work the same in a controller, a job, or a console command.

**With an admin.** Add the [Filament plugin](filament/index.md) to a Filament panel and you get resources, dashboards, and relation managers for each installed module, without building screens yourself.

You can mix the two: use the admin for your team and the actions for your product's own flows.

## Where to start

1. [Install the packages](installation.md) and run the migrations.
2. Read [Configuration](configuration.md) for the settings shared by every module: the user model, public routes, API tokens, and rate limits.
3. Learn the [Core concepts](core/index.md) that the other modules build on.

## Versioning

Focal is pre-1.0. All `focalcrm/*` packages are released together with the same version number, so require the same version of each. Until 1.0, a minor release (for example 0.2 to 0.3) may include breaking changes; patch releases won't. Each release is listed on [GitHub](https://github.com/focalcrm/focal/releases).

## Getting help

Report bugs and ask questions in the [issue tracker](https://github.com/focalcrm/focal/issues). Focal is MIT licensed.
