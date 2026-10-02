---
title: Events
description: The events Focal Core dispatches, what triggers each one, and how to listen for them.
---

Core dispatches plain Laravel events from its action classes. They are dispatched synchronously after the write, inside the same request. None of them implement `ShouldBroadcast` or `ShouldQueue`. Make your listener queued if it does slow work.

All events are in `Focal\Core\Events`.

| Event | Public properties | Dispatched by |
| --- | --- | --- |
| `ContactCreated` | `Contact $contact` | `CreateContactAction`, before auto-association runs |
| `CompanyCreated` | `Company $company` | `CreateCompanyAction`, before enrichment runs. Also when auto-association creates a company. |
| `CompanyEnriched` | `Company $company`, `array $enrichmentData` | `EnrichCompanyAction`, when the driver returned data |
| `RecordsAssociated` | `Association $association` | `AssociateRecordsAction` and `associateWith()`, only when a new link is created |
| `ActivityLogged` | `Activity $activity` | `LogActivityAction` |
| `LifecycleStageChanged` | `Model $record`, `LifecycleStageTransition $transition` | `TransitionLifecycleStageAction` |
| `CustomObjectDefinitionCreated` | `CustomObjectDefinition $definition` | `CreateCustomObjectDefinitionAction` |
| `CustomObjectRecordCreated` | `CustomObjectRecord $record` | `CreateCustomObjectRecordAction` |

## What does not dispatch events

Events come only from the actions above. These do not dispatch them:

- `Contact::create()`, `Company::create()`, and factories.
- `logActivity()`, `logNote()`, `logCall()`, and `logTask()` on a record, including the notes the merge actions log and the churn-risk task the health score action logs.
- Updating `lifecycle_stage` with `update()` or `save()`.
- Merging records.

If you need to react to every change regardless of how it was made, use Eloquent model events or observers on the models instead.

## Listening

Register listeners as you would for any Laravel event. With event discovery, type-hint the event in a listener's `handle()` method:

```php
namespace App\Listeners;

use Focal\Core\Enums\LifecycleStage;
use Focal\Core\Events\LifecycleStageChanged;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifySalesOfNewSql implements ShouldQueue
{
    public function handle(LifecycleStageChanged $event): void
    {
        if ($event->transition->to_stage !== LifecycleStage::SalesQualifiedLead) {
            return;
        }

        // $event->record is the Contact or Company.
    }
}
```

Or register a closure in a service provider:

```php
use Focal\Core\Events\ContactCreated;
use Illuminate\Support\Facades\Event;

Event::listen(function (ContactCreated $event): void {
    $event->contact->logNote('Welcome sequence queued.');
});
```

In tests, use `Event::fake()` and assert on the event classes as usual:

```php
Event::fake([LifecycleStageChanged::class]);

app(TransitionLifecycleStageAction::class)->execute($contact, LifecycleStage::MarketingQualifiedLead);

Event::assertDispatched(LifecycleStageChanged::class);
```
