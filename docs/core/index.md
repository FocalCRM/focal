---
title: Core
description: What the focalcrm/core package provides, the models it ships, and how the other Focal modules build on it.
---

`focalcrm/core` is the foundation every other Focal package depends on. It owns the CRM records (contacts, companies, and custom objects), and the services that work across all of them: custom properties with change history, associations between any two records, the activity timeline, lifecycle stages, lists, and duplicate merging. It has no UI and registers no routes of its own. It only needs `laravel/framework` 12 or 13 and PHP 8.3+.

See [Installation](../installation.md) for installing Focal into your app. Core's service provider, `Focal\Core\CoreServiceProvider`, is auto-discovered. It:

- merges `config/focal-core.php` under the `focal-core` key,
- loads the package migrations (you don't need to publish them),
- registers `LifecycleStateMachine` and `EnrichmentManager` as singletons,
- defines the `focal-public` and `focal-api` rate limiters.

You can publish the config file or the migrations if you want to edit them:

```bash
php artisan vendor:publish --tag=focal-core-config
php artisan vendor:publish --tag=focal-core-migrations
```

## Models

All models live in `Focal\Core\Models`. Table names come from `focal-core.tables.*` (see [Users, routes, and configuration](integration.md#configuration-reference)).

| Model | Default table | What it stores |
| --- | --- | --- |
| `Contact` | `focal_contacts` | People: name, email, phone, job title, lifecycle stage, lead status, lead score, owner, team. Soft-deletes. |
| `Company` | `focal_companies` | Organizations: name, domain, industry, lifecycle stage, health score, account tier and intent fields. Soft-deletes. |
| `PropertyDefinition` | `focal_properties` | The registry of custom properties per entity type. |
| `PropertyHistory` | `focal_property_history` | One row per changed attribute or custom property. |
| `Association` | `focal_associations` | A directed link between any two records. |
| `AssociationType` | `focal_association_types` | Named association types with labels and cardinality. |
| `Activity` | `focal_activities` | Timeline entries: notes, calls, emails, meetings, tasks. |
| `CrmList`, `ListMembership` | `focal_lists`, `focal_list_memberships` | Static and active (criteria-based) lists. |
| `LifecycleStageTransition` | `focal_lifecycle_stage_transitions` | History of lifecycle stage changes with time spent in each stage. |
| `CustomObjectDefinition`, `CustomObjectRecord` | `focal_custom_object_definitions`, `focal_custom_object_records` | Your own record types. |

Behavior is shared through traits in `Focal\Core\Traits`, which you can also add to your own models:

| Trait | Adds | Used by |
| --- | --- | --- |
| `HasCustomProperties` | `getProperty()`, `setProperty()`, `setProperties()`, `whereProperty()` scope | `Contact`, `Company`, `CustomObjectRecord` |
| `AuditsProperties` | Writes `PropertyHistory` on every Eloquent update, `propertyHistory()` relation | `Contact`, `Company`, `CustomObjectRecord` |
| `HasAssociations` | `associateWith()`, `dissociateFrom()`, `isAssociatedWith()`, `getAssociated()`, `getAssociatedByLabel()` | `Contact`, `Company`, `CustomObjectRecord` |
| `HasActivities` | `activities()`, `timeline()`, `logActivity()`, `logNote()`, `logCall()`, `logTask()` | `Contact`, `Company`, `CustomObjectRecord` |
| `HasLifecycleStageTransitions` | `lifecycleTransitions()`, time-in-stage helpers | `Contact`, `Company` |
| `BelongsToTeam` | `forTeam(int $teamId)` scope | `Contact`, `Company`, `AssociationType`, `CustomObjectDefinition`, `CustomObjectRecord`, `LifecycleStageTransition` |

Core has no team model. `team_id` is a plain nullable integer column, and `forTeam()` is the only thing that reads it. Scoping queries by team is up to your app.

## Actions

Writes that have side effects go through action classes in `Focal\Core\Actions`. Resolve them from the container and call `execute()`:

```php
use Focal\Core\Actions\CreateContactAction;

$contact = app(CreateContactAction::class)->execute([
    'first_name' => 'Jane',
    'email' => 'jane@acme.com',
]);
```

Events are only dispatched by actions. Creating a model with `Contact::create()` or calling a trait method such as `logNote()` skips them. See [Events](events.md).

## How the other modules build on Core

The Sales, Marketing, and Service packages don't extend Core's models. They:

- reference `Contact`, `Company`, `Activity`, and the enums directly, and add their own relations at boot with `resolveRelationUsing()`. For example, Sales adds `deals` to `Contact` and `Company`, and Service adds `tickets`.
- resolve the host app's user model through `Focal\Core\Support\UserModel` for owners, assignees, and authors.
- build their route groups with `RouteGroup::attributes()`, protect webhooks with the `RequireApiToken` middleware, and throttle public endpoints with the `focal-public` and `focal-api` limiters. See [Users, routes, and configuration](integration.md).
- add their own columns to `focal_contacts`. For example, Marketing adds `marketing_topics`, `sms_consent`, and `is_unengaged`. These are already fillable and cast on `Contact`, but the columns only exist once that package's migrations have run.

Some Core actions look for tables owned by other modules. For example, merging contacts moves rows in the deals, tickets, and form submission tables when those tables exist.

## Pages in this section

- [Contacts and companies](contacts-and-companies.md): creating records, domain auto-association, enrichment, health scores.
- [Custom properties](custom-properties.md): property definitions, reading and writing values, change history, custom objects.
- [Associations](associations.md): linking records, association types, and cardinality.
- [Activities and the timeline](activities.md): logging activities and reading the timeline.
- [Lifecycle stages](lifecycle-stages.md): the stage state machine, transitions, and funnel velocity.
- [Lists](lists.md): static lists and active lists.
- [Duplicates and merging](duplicates-and-merging.md): finding duplicate contacts and companies, and merging them.
- [Events](events.md): the events Core dispatches.
- [Users, routes, and configuration](integration.md): the user model, route helpers, API tokens, rate limits, and every config key.
