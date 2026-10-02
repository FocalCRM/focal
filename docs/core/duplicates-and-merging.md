---
title: Duplicates and merging
description: Find duplicate contacts and companies, and merge a duplicate into a surviving record.
---

Core has two finder actions that group records with identical values, and two merge actions that fold a secondary record into a primary one and soft-delete the secondary.

## Finding duplicate contacts

`FindDuplicateContactsAction::execute(): array` scans all contacts (soft-deleted ones excluded) and returns groups:

```php
use Focal\Core\Actions\FindDuplicateContactsAction;

$groups = app(FindDuplicateContactsAction::class)->execute();

// [
//     [
//         'match_field' => 'email',
//         'match_value' => 'jane@acme.com',
//         'contacts' => Collection of Contact, ordered by id,
//     ],
// ]
```

It matches on:

1. `email`: contacts with exactly the same value. Whether the comparison ignores case depends on your database collation. `CreateContactAction` lowercases emails, so contacts created through it compare reliably.
2. `phone`: contacts with exactly the same string. Formatting differences such as `555-0100` and `5550100` are not matched. A phone group is skipped if the same set of contacts was already matched by email.

It doesn't match on names, and it takes no arguments, so you can't scope it to one contact or one team.

## Finding duplicate companies

`FindDuplicateCompaniesAction::execute(): array` returns groups with `match_field`, `match_value`, and `companies`. It matches on:

1. `domain`: exactly the same non-empty value.
2. `name`: exactly the same value. A name group is skipped if the same set of companies was already matched by domain.

## Merging contacts

```php
use Focal\Core\Actions\MergeContactsAction;

$survivor = app(MergeContactsAction::class)->execute($primary, $secondary);
```

`execute(Contact $primary, Contact $secondary, array $fieldOverrides = []): Contact` runs in a database transaction:

1. Copies `first_name`, `last_name`, `phone`, `lifecycle_stage`, `lead_status`, `owner_id`, and `team_id` from the secondary where the primary's value is empty.
2. Applies `$fieldOverrides` to the primary.
3. Keeps the higher `lead_score`.
4. Merges `properties`. The primary's values win when both have the same key.
5. Saves the primary. This is a normal save, so [property history](custom-properties.md#change-history) is written.
6. Moves the secondary's activities to the primary.
7. Moves the secondary's associations, in both directions, to the primary. Links that would duplicate one the primary already has are deleted.
8. Moves `contact_id` on the deals table (`focal-sales.tables.deals`, default `focal_deals`), the tickets table (`focal-service.tables.tickets`, default `focal_tickets`), and the form submissions table (`focal-marketing.tables.form_submissions`, default `focal_form_submissions`), if they exist.
9. Logs a "Contact Merged" note on the primary.
10. Soft-deletes the secondary.

It returns the refreshed primary.

```php
$survivor = app(MergeContactsAction::class)->execute(
    $primary,
    $secondary,
    fieldOverrides: ['job_title' => 'CTO'],
);
```

Some data stays with the secondary: list memberships, property history, and lifecycle stage transitions are not moved. Re-add the primary to static lists yourself if needed.

To merge every group the finder returns, keeping the oldest record of each group:

```php
use Focal\Core\Actions\FindDuplicateContactsAction;
use Focal\Core\Actions\MergeContactsAction;

foreach (app(FindDuplicateContactsAction::class)->execute() as $group) {
    $survivor = $group['contacts']->shift();

    foreach ($group['contacts'] as $duplicate) {
        $survivor = app(MergeContactsAction::class)->execute($survivor, $duplicate);
    }
}
```

## Merging companies

`MergeCompaniesAction::execute(Company $primary, Company $secondary, array $fieldOverrides = []): Company` works the same way, with these differences:

- It fills empty `domain`, `phone`, `industry`, `account_tier`, `owner_id`, and `team_id` from the secondary.
- It keeps the higher `intent_score`, sets `intent_surge` if either record has it, and sets `health_score` to the average of the two.
- It moves `company_id` on the tickets table, if it exists. Deals are not moved.
- It logs a "Company Merged" note on the primary.
- It then runs [`CalculateCustomerHealthScoreAction`](contacts-and-companies.md#customer-health-scores) on the primary, which overwrites the averaged health score. If the company moves into `AtRisk`, this also creates a churn-risk task.

```php
use Focal\Core\Actions\MergeCompaniesAction;

$company = app(MergeCompaniesAction::class)->execute($acme, $acmeInc);
```

Neither merge action dispatches an event of its own.
