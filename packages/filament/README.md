# Focal Filament (`focalcrm/filament`)

> This is a read-only split of the [focalcrm/focal](https://github.com/focalcrm/focal) monorepo. Please open issues and pull requests there.

The unified administrative dashboard and RevOps cockpit for the Focal platform, built on Filament v4. Integrates Core CRM, Sales, Service, Marketing, and Mail Builder into a modular, plug-and-play admin interface with auto-discovering resources, executive cockpits, and dynamic EAV form rendering.

---

## Architecture & Capabilities

```
+-------------------------------------------------------------------------+
|                              FOCAL FILAMENT                             |
|                                                                         |
|  +--------------------+   +--------------------+   +-----------------+  |
|  | Executive Overview |   | Sales Cockpit &    |   | Service Cockpit |  |
|  | & KPI Dashboards   |   | Kanban Board       |   | & SLA Monitors  |  |
|  +--------------------+   +--------------------+   +-----------------+  |
|             \                       |                       /           |
|              v                      v                      v            |
|       +---------------------------------------------------------+       |
|       |               FocalPlugin (Auto-Discovery)              |       |
|       |  Detects installed packages (Core, Sales, Service, etc) |       |
|       +---------------------------------------------------------+       |
|             |                       |                       |           |
|             v                       v                       v           |
|  +--------------------+   +--------------------+   +-----------------+  |
|  | Custom Property    |   | Marketing & ABM    |   | Data Quality &  |  |
|  | Dynamic Builder    |   | Cockpits           |   | Deduplication   |  |
|  +--------------------+   +--------------------+   +-----------------+  |
+-------------------------------------------------------------------------+
```

### Core Features

- **Single-Plugin Registration (`FocalPlugin`):** Register the entire RevOps suite in your Filament panel with one line. Detects which Focal packages are installed and registers matching resources, pages, and widgets dynamically.
- **Dedicated Operational Cockpits:**
  - `ExecutiveOverview`: C-level KPI dashboard displaying pipeline value, win rates, SLA compliance, and marketing ROI.
  - `SalesCockpit`: Rep and manager hub with Kanban deal pipelines, quota tracking, and cadence tasks.
  - `ServiceCockpit`: Real-time support queue monitoring SLA clocks, pending replies, and agent workloads.
  - `MarketingCockpit`: Omnichannel performance tracking, active workflow enrollments, and conversion funnels.
  - `AbmCockpit`: Target account engagement matrices and intent scores.
  - `DataQuality`: Automated deduplication finder, orphaned record scanner, and data hygiene audits.
  - `MarketingAttribution`: Interactive multi-touch revenue attribution modeling explorer.
  - `SenderDomainHealth`: Real-time deliverability diagnostic panel (SPF, DKIM, DMARC, ESP status).
- **Dynamic EAV Field Builder (`CustomPropertyFieldBuilder`):** Inspects dynamic `PropertyDefinition` records and dynamically injects typed form inputs and table columns into Contact and Company resources without requiring code changes.
- **Audience & Navigation Clustering:** Organizes dozens of CRM tools into clean navigation clusters (*CRM Core*, *Sales*, *Help Desk*, *Marketing*, *Analytics*, *Configuration*).

---

## Installation

```bash
composer require focalcrm/filament
```

Register `FocalPlugin` in your Filament Panel Provider (e.g., `app/Providers/Filament/AdminPanelProvider.php`):

```php
use Focal\Filament\FocalPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->plugins([
            FocalPlugin::make(),
        ]);
}
```

---

## Granular Module Control

By default, `FocalPlugin` detects installed packages automatically. You can also explicitly toggle modules:

```php
FocalPlugin::make()
    ->enableCore(true)
    ->enableSales(true)
    ->enableService(false)    // Disable service resources and cockpit
    ->enableMarketing(true)
    ->enableMailBuilder(true)
```

---

## Available Filament Resources & Pages

### Core Resources
- `ContactResource`: Manage contacts, lifecycle transitions, polymorphic associations, and custom properties.
- `CompanyResource`: Manage accounts, corporate domains, health scores, and associated contacts.
- `PropertyDefinitionResource`: Admin interface for creating dynamic EAV fields.
- `CrmListResource`: Build and manage dynamic/static contact lists.

### Sales Resources
- `DealResource`: Interactive Kanban board and tabular views with rotting indicators and deal health scores.
- `PipelineResource`: Configure sales stages, probabilities, rotting limits, and stage gate requirements.
- `QuoteResource`: CPQ proposal builder with line items, tax, and preview links.
- `SalesQuotaResource`: Manage rep quotas across monthly, quarterly, and annual intervals.
- `SalesSequenceResource`: Design multi-touch outbound cadences.
- `SalesPlaybookResource`: Script objection handling and question checklists for sales reps.
- `SalesMeetingLinkResource`: Manage rep calendar booking availability.
- `LeadRoutingRuleResource`: Configure lead assignment strategies.

### Service Resources
- `TicketResource`: Multi-channel ticket management with threaded replies, internal notes, and SLA timers.
- `SlaPolicyResource`: Define SLA targets by priority with business-hours schedules.
- `TicketRoutingRuleResource`: Configure automated ticket routing.
- `KnowledgeArticleResource`: Author public self-service documentation.
- `CannedResponseResource`: Manage quick macro responses with variable tokens.

### Marketing Resources
- `CampaignResource`: Schedule broadcast and A/B test marketing campaigns.
- `MarketingWorkflowResource`: Configure automated drip workflows.
- `MarketingFormResource`: Build lead capture forms with progressive profiling.
- `LandingPageResource`: Publish responsive landing pages.
- `MarketingAssetResource`: Track downloadable content and gated whitepapers.
- `MarketingEventResource`: Manage webinars and event registrations.
- `MarketingSubscriptionResource`: Configure subscription topics and consent preferences.
- `LeadScoringRuleResource`: Configure scoring point rules and inactivity decay.
- `NpsSurveyResource`: Manage Net Promoter Score surveys and track satisfaction.
- `AdAudienceSyncResource`: Manage sync to Meta, Google, and LinkedIn.

---

## Testing

```bash
vendor/bin/pest packages/filament/tests --compact
```
