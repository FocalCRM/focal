---
title: Pipelines and stages
description: Define sales pipelines and their stages, and require data or run actions when a deal enters a stage.
---

A pipeline is an ordered list of stages that deals move through. Each stage carries a win probability used for forecasting, optional closed won or closed lost flags, and an optional rotting threshold. Stage automations let you block a deal from entering a stage until it has the data you need, or create follow-up work when it arrives.

## Pipelines

`Odden\Sales\Models\Pipeline` has these fillable attributes:

| Attribute | Type | Notes |
| --- | --- | --- |
| `name` | string | |
| `code` | string | Unique across all pipelines. |
| `is_default` | bool | Defaults to `false`. Nothing enforces a single default pipeline. |
| `is_active` | bool | Defaults to `true`. |
| `team_id` | int, nullable | From Core's `BelongsToTeam` trait. |

```php
use Odden\Sales\Models\Pipeline;

$pipeline = Pipeline::create([
    'name' => 'New Business',
    'code' => 'new_business',
    'is_default' => true,
]);

$default = Pipeline::query()->default()->active()->first();
```

Relations and helpers:

- `stages()`: the pipeline's stages, ordered by `sort_order`.
- `deals()`: all deals in the pipeline.
- `defaultStage(): ?PipelineStage`: the first stage (by `sort_order`) that is neither closed won nor closed lost. Use it as the starting stage for new deals.
- `addStage(array $attributes): PipelineStage`: creates a stage. If you don't pass `sort_order`, it uses the current highest `sort_order` plus 10.
- `forecast(): array`: forecast metrics for this pipeline. See [Deal health, forecasts, and quotas](health-and-forecasting.md#pipeline-forecast).

## Stages

`Odden\Sales\Models\PipelineStage` attributes:

| Attribute | Type | Notes |
| --- | --- | --- |
| `pipeline_id` | int | |
| `name` | string | |
| `code` | string | Unique within a pipeline. |
| `probability` | int | Win probability (0–100) used for the weighted forecast. Defaults to `0`. |
| `sort_order` | int | Order within the pipeline. |
| `is_closed_won` | bool | Moving a deal here sets its status to `won`. |
| `is_closed_lost` | bool | Moving a deal here sets its status to `lost`. |
| `rot_after_days` | int, nullable | Open deals in this stage for this many days or more count as rotting. `null` or `0` turns it off. |

```php
$discovery = $pipeline->addStage([
    'name' => 'Discovery',
    'code' => 'discovery',
    'probability' => 20,
    'rot_after_days' => 14,
]);

$proposal = $pipeline->addStage([
    'name' => 'Proposal',
    'code' => 'proposal',
    'probability' => 60,
    'rot_after_days' => 10,
]);

$won = $pipeline->addStage([
    'name' => 'Closed Won',
    'code' => 'closed_won',
    'probability' => 100,
    'is_closed_won' => true,
]);

$lost = $pipeline->addStage([
    'name' => 'Closed Lost',
    'code' => 'closed_lost',
    'is_closed_lost' => true,
]);

$pipeline->defaultStage(); // Discovery
$won->isClosed();          // true
```

Each pipeline needs one closed won stage and one closed lost stage if you want to use `Deal::markWon()` and `Deal::markLost()`. Without them those methods throw a `RuntimeException`. See [Deals](deals.md#won-and-lost).

`PipelineStage` also has `pipeline()`, `deals()` (deals currently in the stage), and `automations()` (ordered by `sort_order`).

## Stage automations

An `Odden\Sales\Models\StageAutomation` is a rule attached to a stage. Rules run when a deal is moved into the stage with `Deal::moveToStage()`, `markWon()`, `markLost()`, or `ChangeDealStageAction`. They do not run when you create a deal directly in a stage or update `stage_id` yourself.

| Attribute | Type | Notes |
| --- | --- | --- |
| `stage_id` | int | |
| `name` | string | |
| `event_trigger` | string | Defaults to `enter_stage`, the only trigger that is evaluated. |
| `action_type` | `StageAutomationActionType` | What the rule does. |
| `action_payload` | array, nullable | Options for the action. |
| `is_active` | bool | Defaults to `true`. Inactive rules are skipped. |
| `sort_order` | int | Rules run in ascending order. |

### Action types

`Odden\Sales\Enums\StageAutomationActionType` has these cases:

| Case | Value | Behavior |
| --- | --- | --- |
| `RequireAssociatedContact` | `require_associated_contact` | Throws unless the deal has at least one associated contact. |
| `RequireDealProducts` | `require_deal_products` | Throws unless the deal has at least one `DealProduct`. |
| `RequireActiveQuote` | `require_active_quote` | Throws unless the deal has a quote with status `draft`, `sent`, `approved`, or `accepted`. |
| `RequireAcceptedQuote` | `require_accepted_quote` | Throws unless the deal has an `accepted` quote. |
| `CreateTask` | `create_task` | Logs a pending task activity on the deal. |
| `NotifyOwner` | `notify_owner` | If the deal has an `owner_id`, logs a note activity on the deal. It does not send a notification. |

The `Require*` types are stage requirements. When one fails, the action throws `Odden\Sales\Exceptions\StageRequirementException` (a `RuntimeException`). The stage change runs in a database transaction, so the deal stays where it was and anything an earlier rule created is rolled back.

`CreateTask` reads two optional payload keys:

- `task_subject`: the task title. Defaults to `Follow up: {deal name} in [{stage name}]`.
- `due_days`: days from now until the task is due. Defaults to `2`.

```php
use Odden\Core\Models\Contact;
use Odden\Sales\Enums\StageAutomationActionType;
use Odden\Sales\Exceptions\StageRequirementException;
use Odden\Sales\Models\StageAutomation;

StageAutomation::create([
    'stage_id' => $proposal->id,
    'name' => 'Proposal needs a contact',
    'action_type' => StageAutomationActionType::RequireAssociatedContact,
]);

StageAutomation::create([
    'stage_id' => $proposal->id,
    'name' => 'Proposal follow-up',
    'action_type' => StageAutomationActionType::CreateTask,
    'action_payload' => ['task_subject' => 'Walk the buyer through the proposal', 'due_days' => 3],
    'sort_order' => 10,
]);

try {
    $deal->moveToStage($proposal);
} catch (StageRequirementException $e) {
    // "Stage [Proposal] requires at least one associated contact."
    report($e);
}

$contact = Contact::query()->whereEmail('dana@acme.test')->firstOrFail();

$deal->associateWith($contact);
$deal->moveToStage($proposal); // Passes, and logs the follow-up task on the deal.
```

Put requirement rules before action rules (lower `sort_order`) so a task is never created for a move that is then rejected. The transaction rolls it back anyway, but ordering keeps the intent clear.

Stage requirements also apply to quote acceptance, which moves the deal to the closed won stage. See [Quotes](quotes.md#what-acceptance-does).
