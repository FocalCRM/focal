---
title: Lists
description: Group contacts or companies into static lists, or into active lists whose members are recalculated from criteria.
---

`Odden\Core\Models\CrmList` (table `odden_lists`) is a named list of contacts or companies. Membership rows are `ListMembership` records in `odden_list_memberships`. A list has a `type` from `Odden\Core\Enums\ListType`:

| Case | Value | `label()` |
| --- | --- | --- |
| `Static` | `static` | Static List |
| `Active` | `active` | Active (Smart) List |

| Column | Notes |
| --- | --- |
| `name`, `description` | |
| `entity_type` | `contact` (default) or `company`. Active lists evaluate companies for `company` and contacts for any other value. |
| `type` | `ListType`, default `static`. |
| `criteria` | JSON array of rules, for active lists. |
| `created_by_id` | Your user model. `creator()` is a `BelongsTo` relation. |

## Static lists

Add and remove members yourself:

```php
use Odden\Core\Enums\ListType;
use Odden\Core\Models\CrmList;

$list = CrmList::create([
    'name' => 'Webinar attendees',
    'entity_type' => 'contact',
    'type' => ListType::Static,
]);

$list->addMember($contact);     // ListMembership, created once
$list->hasMember($contact);     // true
$list->contacts;                // Collection of contacts
$list->removeMember($contact);  // number of rows deleted
```

| Method | Behavior |
| --- | --- |
| `addMember(Model $record): ListMembership` | `firstOrCreate`, so adding twice is harmless. Sets `added_at`. |
| `removeMember(Model $record): int` | Deletes the membership. |
| `hasMember(Model $record): bool` | |
| `memberships()` | `HasMany` `ListMembership`. Each membership has `list()` and a `member()` morph relation. |
| `contacts()`, `companies()` | `BelongsToMany` through the memberships table, with `added_at` on the pivot. |
| `forEntity(string $entityType)` scope | Filters by `entity_type`. |

`addMember()` accepts any model, but only `contacts()` and `companies()` have typed relations. Use `memberships()` for other models.

## Active lists

An active list stores rules in `criteria`. `syncActiveMembers()` runs the rules, then adds and removes memberships so they match. It returns the number of matching records. On a static list it does nothing and returns `0`.

```php
$list = CrmList::create([
    'name' => 'Engaged EU leads',
    'entity_type' => 'contact',
    'type' => ListType::Active,
    'criteria' => [
        ['property' => 'lifecycle_stage', 'operator' => 'in', 'value' => ['lead', 'marketing_qualified_lead']],
        ['property' => 'region', 'operator' => '=', 'value' => 'EU'],
        ['property' => 'phone', 'operator' => 'is_not_null'],
    ],
]);

$count = $list->syncActiveMembers();
```

Membership doesn't update on its own when records change. The Filament package syncs a list when you create or edit it, and Marketing syncs a list before sending a campaign or an ad audience to it. Otherwise, call `syncActiveMembers()` yourself when you need fresh results, for example from a scheduled command. It calls `EvaluateActiveListAction::execute(CrmList $list): int`, which you can also call directly.

### Rules

Each rule is an array with `property`, `operator` (default `=`), and `value`. All rules must match (AND). Rules with an empty `property` are skipped.

| Operator | Condition |
| --- | --- |
| `=` | equal (also used for any unknown operator) |
| `!=`, `>`, `>=`, `<`, `<=` | comparison |
| `contains` | `LIKE %value%` |
| `in`, `not_in` | `value` is an array |
| `is_null`, `is_not_null` | `value` is ignored |

How `property` is resolved:

- If it isn't a column on the contacts or companies table, it's matched against the custom property: `properties->{property}`.
- If it's a column, the rule matches when either the column or a custom property with the same name matches. For example, `lead_score >= 50` also matches a contact whose `properties.lead_score` is 50 or more.

Two extra properties apply to contact lists when the Marketing package's tables exist:

- `has_downloaded_asset` (`true` or `false`): whether the contact has a row in the asset downloads table.
- `has_attended_event` (`true` or `false`): whether the contact has an event registration with status `attended`.

### Company lists

```php
$accounts = CrmList::create([
    'name' => 'Tier 1 accounts',
    'entity_type' => 'company',
    'type' => ListType::Active,
    'criteria' => [
        ['property' => 'account_tier', 'operator' => '=', 'value' => 'tier_1'],
    ],
]);

$accounts->syncActiveMembers();
$accounts->companies;
```

Soft-deleted contacts and companies never match a rule, so syncing removes them from active lists. Static lists keep their memberships until you remove them.
