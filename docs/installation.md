---
title: Installation
description: Install the Focal packages, run the migrations, and schedule the commands each module needs.
---

## Requirements

- PHP 8.3 or newer
- Laravel 12 or 13
- A database supported by Laravel. Focal is tested on SQLite and PostgreSQL.
- Filament 5.9 or newer, only if you install the [Filament admin](filament/index.md)

## Install the packages

Require the modules you want. Each one requires `focalcrm/core`, so you don't need to list it unless you only want Core:

```bash
composer require focalcrm/sales focalcrm/service focalcrm/marketing
```

Every package registers itself through Laravel's package discovery and loads its own migrations. Run them:

```bash
php artisan migrate
```

All Focal tables are prefixed with `focal_`. Table names are configurable per package (the `tables` key in each config file) if they would clash with your own.

## Add the Filament admin

```bash
composer require focalcrm/filament
```

Then register the plugin in your panel provider. See [Filament admin](filament/index.md) for the details.

## Publish configuration (optional)

Each package works with its defaults. Publish a config file only when you need to change something:

```bash
php artisan vendor:publish --tag=focal-core-config
php artisan vendor:publish --tag=focal-sales-config
php artisan vendor:publish --tag=focal-service-config
php artisan vendor:publish --tag=focal-marketing-config
```

Migrations can be published the same way with the `focal-core-migrations`, `focal-sales-migrations`, `focal-service-migrations`, and `focal-marketing-migrations` tags, if you want to modify them before they run.

## Schedule the commands

Several features run on a schedule: campaign sends, workflow steps, SLA checks, quote expiry, and more. Focal doesn't register a schedule for you, so add the commands for the modules you installed to `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

// Marketing
Schedule::command('marketing:dispatch-scheduled')->everyMinute()->withoutOverlapping()->onOneServer();
Schedule::command('marketing:process-workflows')->everyFiveMinutes()->withoutOverlapping()->onOneServer();
Schedule::command('marketing:evaluate-ab-tests')->hourly()->withoutOverlapping()->onOneServer();
Schedule::command('marketing:decay-lead-scores')->dailyAt('03:00')->withoutOverlapping()->onOneServer();
Schedule::command('marketing:sunset-subscribers')->dailyAt('03:30')->withoutOverlapping()->onOneServer();

// Sales
Schedule::command('sales:process-cadences')->everyFifteenMinutes()->withoutOverlapping()->onOneServer();
Schedule::command('sales:expire-quotes')->dailyAt('01:00')->withoutOverlapping()->onOneServer();

// Service
Schedule::command('service:check-sla')->everyFiveMinutes()->withoutOverlapping()->onOneServer();
Schedule::command('service:run-automations')->hourly()->withoutOverlapping()->onOneServer();
```

These are the frequencies Focal Cloud uses. `marketing:dispatch-scheduled` should run every minute, because campaigns that send in each recipient's local time are released in five-minute windows. `onOneServer()` needs a cache store shared by all your servers; drop it if you run a single server.

Then make sure the scheduler runs, with `php artisan schedule:work` locally or a cron entry for `php artisan schedule:run` in production.

## Configure mail

Focal sends these emails with your application's default mailer, synchronously, from the request or command that triggers them:

- Service ticket notifications: confirmation, replies, resolution with a CSAT survey, and SLA breach alerts to the ticket owner
- Marketing campaign proofs and the [transactional email API](marketing/transactional-email.md)

Configure a mailer in `config/mail.php` before you use them.

Campaigns, workflow email steps, and sales sequence email steps don't deliver mail yet: they compile and record each message without sending it. See [delivering campaign messages](marketing/campaigns.md#delivering-the-messages) for how to send them yourself.

## Next steps

- [Configuration](configuration.md): the user model, public routes, API tokens, and rate limits
- [Core concepts](core/index.md)
