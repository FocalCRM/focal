# Odden Core (`getodden/crm-core`)

> This is a read-only split of the [getodden/crm](https://github.com/getodden/crm) monorepo. Please open issues and pull requests there.

The headless CRM foundation engine for the Odden RevOps platform. Manages contacts, companies, extensible custom properties (EAV), polymorphic associations, activity audit timelines, list segmentation, and lifecycle stage state transitions.

---

## Architecture & Capabilities

```
+-------------------------------------------------------------------------+
|                               ODDEN CORE                                |
|                                                                         |
|  +--------------------+   +--------------------+   +-----------------+  |
|  |      Contacts      |   |     Companies      |   | Custom Objects  |  |
|  +--------------------+   +--------------------+   +-----------------+  |
|             \                       /                       /           |
|              v                     v                       v            |
|       +---------------------------------------------------------+       |
|       |               Polymorphic Association Engine            |       |
|       |       (1:1, 1:N, M:N with directional cardinalities)    |       |
|       +---------------------------------------------------------+       |
|             |                       |                       |           |
|             v                       v                       v           |
|  +--------------------+   +--------------------+   +-----------------+  |
|  | Custom Properties  |   | Activity Timelines |   | Lifecycle State |  |
|  |  (EAV + Audit Log) |   |  (Calls, Meetings) |   | Machine & Health|  |
|  +--------------------+   +--------------------+   +-----------------+  |
+-------------------------------------------------------------------------+
```

### Core Features

- **Extensible EAV Properties:** Dynamically define and validate custom properties (`text`, `number`, `date`, `datetime`, `boolean`, `select`, `multiselect`, `json`) across any CRM record, complete with full historical audit trails (`PropertyHistory`).
- **Polymorphic Association Engine:** Connect any record to any other record (`Contact`, `Company`, `Deal`, `Ticket`, or custom object) with directional types and enforced cardinality (`OneToOne`, `OneToMany`, `ManyToMany`).
- **Unified Activity Timeline:** Log calls, notes, emails, meetings, tasks, WhatsApp, SMS, and LinkedIn messages with polymorphic subject attachment.
- **Strict Lifecycle State Machine:** Enforce deterministic progression (`Subscriber` -> `Lead` -> `MQL` -> `SQL` -> `Opportunity` -> `Customer` -> `Evangelist`), with full transition tracking and velocity metrics.
- **Automated Association & Domain Extraction:** Automatically match inbound contacts to companies using corporate email domains while filtering out 4,000+ public freemail providers (`gmail.com`, `yahoo.com`, etc.).
- **Deduplication & Record Merging:** Identify contact/company duplicates via exact or fuzzy matchers, and safely merge secondary records into a primary survivor while repointing associations, activities, and property histories.
- **Dynamic & Static Segmentation:** Filter and maintain active CRM audience lists (`CrmList`) using declarative condition criteria.
- **Customer Health Scoring:** Calculate multidimensional health scores (`Healthy`, `At Risk`, `Critical`) derived from activity recency, open support tickets, and deal interactions.

---

## Installation

```bash
composer require getodden/crm-core
```

Publish migrations and configuration (optional):

```bash
php artisan vendor:publish --tag=odden-core-migrations
php artisan vendor:publish --tag=odden-core-config
```

Run migrations:

```bash
php artisan migrate
```

---

## Quick Start & Code Examples

### 1. Creating Contacts & Automatic Company Association

```php
use Odden\Core\Actions\CreateContactAction;
use Odden\Core\Enums\LifecycleStage;

$contact = app(CreateContactAction::class)->execute([
    'first_name' => 'Jane',
    'last_name' => 'Doe',
    'email' => 'jane@acme.corp',
    'lifecycle_stage' => LifecycleStage::Lead,
    'custom_properties' => [
        'job_title' => 'VP of Engineering',
        'annual_cloud_budget' => 150000,
    ],
]);

// If Acme Corp exists with domain "acme.corp", Jane is automatically associated.
```

### 2. Polymorphic Associations

```php
use Odden\Core\Actions\AssociateRecordsAction;
use Odden\Core\Models\AssociationType;
use Odden\Core\Enums\AssociationCardinality;

$type = AssociationType::firstOrCreate([
    'name' => 'billing_contact',
    'label' => 'Billing Contact',
    'cardinality' => AssociationCardinality::OneToOne,
]);

app(AssociateRecordsAction::class)->execute(
    from: $company,
    to: $contact,
    type: $type
);
```

### 3. Lifecycle Stage State Transitions

```php
use Odden\Core\Actions\TransitionLifecycleStageAction;
use Odden\Core\Enums\LifecycleStage;

// Transitions validate against allowed paths and record a LifecycleStageTransition audit entry
app(TransitionLifecycleStageAction::class)->execute(
    record: $contact,
    newStage: LifecycleStage::Mql,
    actorId: auth()->id()
);
```

### 4. Logging Timeline Activities

```php
use Odden\Core\Actions\LogActivityAction;
use Odden\Core\Enums\ActivityType;

app(LogActivityAction::class)->execute([
    'type' => ActivityType::Call,
    'subject' => 'Q4 Contract Review',
    'body' => 'Client agreed to proceed with annual enterprise tier.',
    'subjectable' => $contact,
    'author_id' => auth()->id(),
    'metadata' => [
        'call_duration_seconds' => 940,
        'recording_url' => 'https://storage.acme.corp/calls/rec_9281.mp3',
    ],
]);
```

### 5. Deduplication & Record Merging

```php
use Odden\Core\Actions\FindDuplicateContactsAction;
use Odden\Core\Actions\MergeContactsAction;

// Discover duplicates by email or normalized name
$duplicates = app(FindDuplicateContactsAction::class)->execute($contact);

if ($duplicates->isNotEmpty()) {
    // Merges secondary into primary, re-parenting associations, logs, and properties
    app(MergeContactsAction::class)->execute(
        primary: $contact,
        secondary: $duplicates->first(),
        actorId: auth()->id()
    );
}
```

---

## Data Models & Schema Reference

| Model | Table | Responsibility |
| :--- | :--- | :--- |
| `Contact` | `contacts` | Individual persons, emails, phones, lifecycle stages, and custom properties. |
| `Company` | `companies` | Organizations, verified corporate domains, employee sizes, and health scores. |
| `PropertyDefinition` | `property_definitions` | Dynamic property registry specifying data types, validation rules, and target entities. |
| `PropertyHistory` | `property_histories` | Point-in-time EAV change logs with actor attribution. |
| `Association` | `associations` | Polymorphic graph connecting any two CRM records. |
| `AssociationType` | `association_types` | Relationship taxonomy defining cardinality and directional semantics. |
| `Activity` | `activities` | Time-stamped touchpoints (calls, meetings, notes, tasks) attached to entities. |
| `CrmList` | `crm_lists` | Static or dynamic segmentation rules and filter definitions. |
| `LifecycleStageTransition` | `lifecycle_stage_transitions` | Immutable history of record lifecycle stage movements. |
| `CustomObjectDefinition` | `custom_object_definitions` | Schema definitions for custom user-created entities. |
| `CustomObjectRecord` | `custom_object_records` | Concrete instances of user-defined entities. |

---

## Testing

```bash
vendor/bin/pest packages/core/tests --compact
```
