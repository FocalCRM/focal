---
title: Customizing and extending
description: Add your own resources next to Odden's, replace an Odden resource with a subclass, override the plugin's Blade views, and make sure the custom pages are styled.
---

`OddenPlugin` has no options for changing its resources (see [Configuration and navigation](configuration.md)). To customize the admin, you add your own Filament classes next to the plugin, or you register Odden's classes yourself and swap in subclasses where you need changes.

## Adding your own resources and pages

The plugin only adds to the panel, so your own resources, pages and widgets work alongside it as usual:

```php
use Filament\Pages\Dashboard;
use Odden\Filament\OddenPlugin;

return $panel
    // ...
    ->plugins([
        OddenPlugin::make(),
    ])
    ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
    ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
    ->pages([
        Dashboard::class,
    ]);
```

Your resources can use Odden models directly, for example a resource for `Odden\Core\Models\Activity`. Avoid slugs the plugin already uses (`contacts`, `companies`, `deals` and so on, listed in [URLs and route names](configuration.md#urls-and-route-names)). Filament derives a resource's slug from its class name, so `App\Filament\Resources\ContactResource` would also get `contacts` and clash with the plugin's routes.

To put your items into Odden's navigation groups, use the same group labels: `CRM`, `Sales`, `Service`, `Marketing`, `Executive` or `Settings`.

## Replacing a resource

A Filament panel can't unregister a resource that a plugin added. To change an Odden resource, don't use `OddenPlugin` on that panel. Register the Odden resources and pages you want yourself, and use your own subclass for the one you're changing.

Start with the resource subclass. Filament derives the slug from the class name, so naming it `ContactResource` keeps the `contacts` URLs and route names the other Odden resources link to. With a different class name, set `protected static ?string $slug = 'contacts';`.

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages\CreateContact;
use App\Filament\Resources\ContactResource\Pages\EditContact;
use App\Filament\Resources\ContactResource\Pages\ListContacts;
use App\Filament\Resources\ContactResource\Pages\ViewContact;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Odden\Filament\Resources\ContactResource as OddenContactResource;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ContactResource extends OddenContactResource
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

Override `getPages()` and subclass the pages as well. Each Odden page class sets `protected static string $resource` to the Odden resource, and Filament pages call their own resource's `table()`, `form()` and `getEloquentQuery()`. If your resource only overrides navigation properties (`$navigationGroup`, `$navigationLabel`, `$navigationSort`, `$navigationIcon`), the original pages are enough. But a `table()`, `form()` or `getEloquentQuery()` override has no effect unless the pages point at your class:

```php
<?php

namespace App\Filament\Resources\ContactResource\Pages;

use App\Filament\Resources\ContactResource;
use Odden\Filament\Resources\ContactResource\Pages\ListContacts as OddenListContacts;

class ListContacts extends OddenListContacts
{
    protected static string $resource = ContactResource::class;
}
```

Create `CreateContact`, `EditContact` and `ViewContact` the same way, each extending the matching class in `Odden\Filament\Resources\ContactResource\Pages`. With all four pages pointing at your resource, the `getEloquentQuery()` scope above applies to the table and to the view and edit URLs. A contact owned by someone else then returns a 404.

Then register everything on the panel in place of the plugin. This example rebuilds the core part of the plugin:

```php
use App\Filament\Resources\ContactResource;
use Odden\Filament\Pages\DataQuality;
use Odden\Filament\Pages\ExecutiveOverview;
use Odden\Filament\Resources\CompanyResource;
use Odden\Filament\Resources\CrmListResource;
use Odden\Filament\Resources\PropertyDefinitionResource;

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

The plugin's custom pages and modals render Blade views from the `odden-filament` namespace:

| View | Used by |
| --- | --- |
| `odden-filament::pages.executive-overview` | `ExecutiveOverview` |
| `odden-filament::pages.data-quality` | `DataQuality` |
| `odden-filament::pages.sales-cockpit` | `SalesCockpit` |
| `odden-filament::pages.deal-kanban` | `DealResource` board page |
| `odden-filament::pages.service-cockpit` | `ServiceCockpit` |
| `odden-filament::pages.service-analytics` | `ServiceAnalytics` |
| `odden-filament::pages.ticket-kanban` | `TicketResource` board page |
| `odden-filament::pages.marketing-cockpit` | `MarketingCockpit` |
| `odden-filament::pages.abm-cockpit` | `AbmCockpit` |
| `odden-filament::pages.marketing-attribution` | `MarketingAttribution` |
| `odden-filament::pages.campaign-benchmarking` | `CampaignBenchmarking` |
| `odden-filament::pages.marketing-calendar` | `MarketingCalendar` |
| `odden-filament::pages.utm-link-builder` | `UtmLinkBuilder` |
| `odden-filament::pages.sender-domain-health` | `SenderDomainHealth` |
| `odden-filament::components.ai-briefing-modal` | **AI Briefing** action on contacts and companies |

The package doesn't register any publishable files, so `vendor:publish` has nothing to copy. Laravel still checks your app's `resources/views/vendor/odden-filament` directory first, so you can override a view by copying it there under the same relative path:

```bash
mkdir -p resources/views/vendor/odden-filament/pages
cp vendor/getodden/crm-filament/resources/views/pages/sales-cockpit.blade.php \
   resources/views/vendor/odden-filament/pages/sales-cockpit.blade.php
```

Laravel only picks up the override directory if it exists when the application boots. The views call public properties and methods on the page classes (for example `$this->guidedActions` or `wire:click="advanceEnrollment(...)"`), so check your copy whenever you upgrade the package.

Some actions also render views from the module packages, such as `odden-marketing::template-preview` and `odden-sales::deals.health-score-modal`. Override those in `resources/views/vendor/odden-marketing` and `resources/views/vendor/odden-sales` in the same way.

## Styling the custom pages

Most of the custom pages (the cockpits, Executive Overview, Data Quality, Service Analytics and the two boards) carry most of their styling in a `<style>` block inside the view, with only a few Tailwind utility classes.

The Campaign Calendar, Campaign Benchmarking, Attribution & ROI, UTM Link Builder and Domain Health pages are styled almost entirely with Tailwind utility classes. Filament's default stylesheet only contains the classes Filament itself uses, so for all of these pages to look as intended, create a custom Filament theme and add the package's views to its sources. Filament's theme command creates `resources/css/filament/{panel-id}/theme.css` and walks you through registering it:

```bash
php artisan make:filament-theme
```

Then add this line to `theme.css`, next to the `@source` lines Filament generated:

```css
@source '../../../../vendor/getodden/crm-filament/resources/views/**/*';
```

The path is relative to `theme.css`. Rebuild your assets afterwards.
