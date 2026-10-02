---
title: Customizing and extending
description: Add your own resources next to Focal's, replace a Focal resource with a subclass, override the plugin's Blade views, and make sure the custom pages are styled.
---

`FocalPlugin` has no options for changing its resources (see [Configuration and navigation](configuration.md)). To customize the admin, you add your own Filament classes next to the plugin, or you register Focal's classes yourself and swap in subclasses where you need changes.

## Adding your own resources and pages

The plugin only adds to the panel, so your own resources, pages and widgets work alongside it as usual:

```php
use Filament\Pages\Dashboard;
use Focal\Filament\FocalPlugin;

return $panel
    // ...
    ->plugins([
        FocalPlugin::make(),
    ])
    ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
    ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
    ->pages([
        Dashboard::class,
    ]);
```

Your resources can use Focal models directly, for example a resource for `Focal\Core\Models\Activity`. Avoid slugs the plugin already uses (`contacts`, `companies`, `deals` and so on, listed in [URLs and route names](configuration.md#urls-and-route-names)). Filament derives a resource's slug from its class name, so `App\Filament\Resources\ContactResource` would also get `contacts` and clash with the plugin's routes.

To put your items into Focal's navigation groups, use the same group labels: `CRM`, `Sales`, `Service`, `Marketing`, `Executive` or `Settings`.

## Replacing a resource

A Filament panel can't unregister a resource that a plugin added. To change a Focal resource, don't use `FocalPlugin` on that panel. Register the Focal resources and pages you want yourself, and use your own subclass for the one you're changing.

Start with the resource subclass. Filament derives the slug from the class name, so naming it `ContactResource` keeps the `contacts` URLs and route names the other Focal resources link to. With a different class name, set `protected static ?string $slug = 'contacts';`.

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages\CreateContact;
use App\Filament\Resources\ContactResource\Pages\EditContact;
use App\Filament\Resources\ContactResource\Pages\ListContacts;
use App\Filament\Resources\ContactResource\Pages\ViewContact;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Focal\Filament\Resources\ContactResource as FocalContactResource;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ContactResource extends FocalContactResource
{
    protected static UnitEnum|string|null $navigationGroup = 'People';

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->pushColumns([
                TextColumn::make('owner.name')->label('Owner'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('owner_id', auth()->id());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContacts::route('/'),
            'create' => CreateContact::route('/create'),
            'view' => ViewContact::route('/{record}'),
            'edit' => EditContact::route('/{record}/edit'),
        ];
    }
}
```

Override `getPages()` and subclass the pages as well. Each Focal page class sets `protected static string $resource` to the Focal resource, and Filament pages call their own resource's `table()`, `form()` and `getEloquentQuery()`. If your resource only overrides navigation properties (`$navigationGroup`, `$navigationLabel`, `$navigationSort`, `$navigationIcon`), the original pages are enough. But a `table()`, `form()` or `getEloquentQuery()` override has no effect unless the pages point at your class:

```php
<?php

namespace App\Filament\Resources\ContactResource\Pages;

use App\Filament\Resources\ContactResource;
use Focal\Filament\Resources\ContactResource\Pages\ListContacts as FocalListContacts;

class ListContacts extends FocalListContacts
{
    protected static string $resource = ContactResource::class;
}
```

Create `CreateContact`, `EditContact` and `ViewContact` the same way, each extending the matching class in `Focal\Filament\Resources\ContactResource\Pages`. With all four pages pointing at your resource, the `getEloquentQuery()` scope above applies to the table and to the view and edit URLs. A contact owned by someone else then returns a 404.

Then register everything on the panel in place of the plugin. This example rebuilds the core part of the plugin:

```php
use App\Filament\Resources\ContactResource;
use Focal\Filament\Pages\DataQuality;
use Focal\Filament\Pages\ExecutiveOverview;
use Focal\Filament\Resources\CompanyResource;
use Focal\Filament\Resources\CrmListResource;
use Focal\Filament\Resources\PropertyDefinitionResource;

return $panel
    // ...
    ->resources([
        ContactResource::class,
        CompanyResource::class,
        CrmListResource::class,
        PropertyDefinitionResource::class,
    ])
    ->pages([
        ExecutiveOverview::class,
        DataQuality::class,
    ]);
```

Add the sales, service and marketing classes the same way. [How modules are detected](configuration.md#how-modules-are-detected) lists what the plugin registers for each module. When you register them yourself, the `class_exists()` checks are up to you.

## Overriding views

The plugin's custom pages and modals render Blade views from the `focal-filament` namespace:

| View | Used by |
| --- | --- |
| `focal-filament::pages.executive-overview` | `ExecutiveOverview` |
| `focal-filament::pages.data-quality` | `DataQuality` |
| `focal-filament::pages.sales-cockpit` | `SalesCockpit` |
| `focal-filament::pages.deal-kanban` | `DealResource` board page |
| `focal-filament::pages.service-cockpit` | `ServiceCockpit` |
| `focal-filament::pages.service-analytics` | `ServiceAnalytics` |
| `focal-filament::pages.ticket-kanban` | `TicketResource` board page |
| `focal-filament::pages.marketing-cockpit` | `MarketingCockpit` |
| `focal-filament::pages.abm-cockpit` | `AbmCockpit` |
| `focal-filament::pages.marketing-attribution` | `MarketingAttribution` |
| `focal-filament::pages.campaign-benchmarking` | `CampaignBenchmarking` |
| `focal-filament::pages.marketing-calendar` | `MarketingCalendar` |
| `focal-filament::pages.utm-link-builder` | `UtmLinkBuilder` |
| `focal-filament::pages.sender-domain-health` | `SenderDomainHealth` |
| `focal-filament::components.ai-briefing-modal` | **AI Briefing** action on contacts and companies |

The package doesn't register any publishable files, so `vendor:publish` has nothing to copy. Laravel still checks your app's `resources/views/vendor/focal-filament` directory first, so you can override a view by copying it there under the same relative path:

```bash
mkdir -p resources/views/vendor/focal-filament/pages
cp vendor/focalcrm/filament/resources/views/pages/sales-cockpit.blade.php \
   resources/views/vendor/focal-filament/pages/sales-cockpit.blade.php
```

Laravel only picks up the override directory if it exists when the application boots. The views call public properties and methods on the page classes (for example `$this->guidedActions` or `wire:click="advanceEnrollment(...)"`), so check your copy whenever you upgrade the package.

Some actions also render views from the module packages, such as `focal-marketing::template-preview` and `focal-sales::deals.health-score-modal`. Override those in `resources/views/vendor/focal-marketing` and `resources/views/vendor/focal-sales` in the same way.

## Styling the custom pages

Most of the custom pages (the cockpits, Executive Overview, Data Quality, Service Analytics and the two boards) carry most of their styling in a `<style>` block inside the view, with only a few Tailwind utility classes.

The Campaign Calendar, Campaign Benchmarking, Attribution & ROI, UTM Link Builder and Domain Health pages are styled almost entirely with Tailwind utility classes. Filament's default stylesheet only contains the classes Filament itself uses, so for all of these pages to look as intended, create a custom Filament theme and add the package's views to its sources. Filament's theme command creates `resources/css/filament/{panel-id}/theme.css` and walks you through registering it:

```bash
php artisan make:filament-theme
```

Then add this line to `theme.css`, next to the `@source` lines Filament generated:

```css
@source '../../../../vendor/focalcrm/filament/resources/views/**/*';
```

The path is relative to `theme.css`. Rebuild your assets afterwards.
