---
title: Workflows
description: Build drip and automation workflows from ordered steps, enroll contacts from triggers, code, or an inbound webhook, and advance delays with the scheduled command.
---

A workflow is a numbered sequence of steps that runs for each enrolled contact: wait, branch on a condition, update the contact, create a deal or task, call a webhook, and so on. Contacts are enrolled by triggers (a form submission, an event attendance), by your code, or by an inbound webhook. Steps run immediately until the workflow reaches a delay; the `marketing:process-workflows` command resumes enrollments whose delay has passed.

## Models

| Model | Table | Purpose |
| --- | --- | --- |
| `Focal\Marketing\Models\MarketingWorkflow` | `focal_marketing_workflows` | The workflow: `name`, `description`, `trigger_type`, `trigger_config`, `is_active` (default `true`), `enrollments_count`, `completed_count`, `created_by_id`. |
| `Focal\Marketing\Models\WorkflowStep` | `focal_marketing_workflow_steps` | One step: `workflow_id`, `step_number`, `type`, `config` (array), `next_step_on_true`, `next_step_on_false`. |
| `Focal\Marketing\Models\WorkflowEnrollment` | `focal_marketing_workflow_enrollments` | A contact's run through a workflow: `current_step_id`, `status`, `next_run_at`, `enrolled_at`, `completed_at`. |
| `Focal\Marketing\Models\WorkflowLog` | `focal_marketing_workflow_logs` | One row per executed step: `action_taken`, `status` (`success`, `skipped`, or `failed`), `details`. |

Relations: `$workflow->steps` (ordered by `step_number`), `$workflow->enrollments`, `$workflow->creator`, `$enrollment->workflow`, `$enrollment->contact`, `$enrollment->currentStep`, `$enrollment->logs`. The package also adds `workflowEnrollments` to `Contact`.

`Focal\Marketing\Enums\WorkflowEnrollmentStatus` has the cases `Active`, `Paused`, `Completed`, and `Exited`. The package itself only sets `active` and `completed`. Only `active` enrollments run, so you can pause or exit one by updating its status.

## Building a workflow

```php
use Focal\Marketing\Enums\WorkflowStepType;
use Focal\Marketing\Enums\WorkflowTriggerType;
use Focal\Marketing\Models\MarketingWorkflow;

$workflow = MarketingWorkflow::create([
    'name' => 'Demo request nurture',
    'trigger_type' => WorkflowTriggerType::FormSubmitted,
    'trigger_config' => ['form_id' => $form->id],
]);

$workflow->steps()->createMany([
    ['step_number' => 1, 'type' => WorkflowStepType::CreateSalesTask, 'config' => ['title' => 'Call new demo request', 'due_in_hours' => 4]],
    ['step_number' => 2, 'type' => WorkflowStepType::Delay, 'config' => ['delay_minutes' => 60 * 24 * 2]],
    ['step_number' => 3, 'type' => WorkflowStepType::Condition, 'config' => ['property' => 'lead_score', 'operator' => '>=', 'value' => 50], 'next_step_on_true' => 4, 'next_step_on_false' => 5],
    ['step_number' => 4, 'type' => WorkflowStepType::UpdateContact, 'config' => ['lifecycle_stage' => 'sales_qualified_lead', 'next_step' => null]],
    ['step_number' => 5, 'type' => WorkflowStepType::Webhook, 'config' => ['url' => 'https://hooks.example.com/focal', 'secret' => 'shared-secret']],
]);
```

When someone submits the form, step 1 creates a task and step 2 schedules the rest for two days later. Then step 3 sends contacts with 50 or more points to step 4, which marks them SQL and ends the workflow, and everyone else to step 5.

### Step order

Steps are addressed by `step_number`, not by id. After a step runs, the enrollment moves to:

- the `next_step` value in the step's `config`, if the key is present (`null` ends the workflow);
- otherwise `step_number + 1`.

If no step has that number, the enrollment is completed. Conditions use `next_step_on_true` and `next_step_on_false` instead, and delays always continue with `step_number + 1`.

## Step types

`Focal\Marketing\Enums\WorkflowStepType`:

| Case | Value | `config` keys | What it does |
| --- | --- | --- | --- |
| `SendEmail` | `send_email` | `template_id`, `subject` (default `Marketing Update`), `body` | Renders the template's `body_html` (or `body`) with the contact's merge tags (values HTML-escaped), logs a `Workflow Email: {subject}` task on the contact, and writes a log entry. It doesn't send an email; see the note below. |
| `SendSms` | `send_sms` | `message` (default `Marketing Update`), `requires_consent` (default `true`) | Sends an SMS through `DispatchSmsAction`. The log status is `success` when delivered and `skipped` otherwise (no phone or no consent). See the note below. |
| `Delay` | `delay` | `delay_minutes` (default 60, minimum 1) | Moves to the next step and sets `next_run_at` that many minutes ahead. If there's no next step, completes the enrollment. |
| `Condition` | `condition` | `property` (default `lead_score`), `operator` (default `>=`), `value` (default 50) | Compares a contact value and branches. See [conditions](#conditions). |
| `UpdateContact` | `update_contact` | `lifecycle_stage` | Sets the contact's lifecycle stage. It's the only supported field. |
| `AssignOwner` | `assign_owner` | `owner_id` | Sets the contact's owner, defaulting to the first user. |
| `CreateDeal` | `create_deal` | `deal_name`, `amount` (default 10000), `pipeline_id`, `stage_id` | Creates an open deal (requires `focalcrm/sales`) owned by the contact's owner and associates it with the contact. Without `pipeline_id`, uses the first pipeline and its first stage. Logged as `skipped` when there's no pipeline. |
| `CreateSalesTask` | `create_sales_task` | `title` (default `High-Priority Lead Follow-up`), `due_in_hours` (default 2) | Logs a task on the contact. |
| `InternalNotification` | `internal_notification` | `message` | Logs an `Internal Alert: {message}` task on the contact. No notification is sent. |
| `Webhook` | `webhook` | `url`, `method` (default `POST`), `secret`, `headers` | Calls a URL. See [outbound webhooks](#outbound-webhook-step). |

Every step writes a `WorkflowLog` row. Each case has `label()` and `getLabel()`.

> **`send_email` doesn't send mail.** The step compiles the message and records it on the timeline and in the workflow log, but no mailable is sent or queued. To email contacts from a workflow, call your own code from a `webhook` step, or send a [transactional email](transactional-email.md) yourself.
>
> **`send_sms` falls through to `assign_owner`.** After the SMS step runs, execution continues into the `assign_owner` code: the contact is assigned to `config.owner_id` or, without one, to the first user, and a second log entry is written. Avoid `send_sms` steps on contacts whose owner you want to keep, or set `owner_id` in the SMS step's `config` to the intended owner.

### Conditions

The `property` is read from the contact:

- `lead_score`: the score;
- `lifecycle_stage`: the stage value, such as `customer`;
- anything else: the custom property of that name.

Operators `>=`, `>`, `<=` and `<` compare as integers. `!=` compares as strings. Any other operator, such as `=` or `==`, tests string equality.

When the condition passes, the enrollment moves to `next_step_on_true`, or `step_number + 1` if that's empty. When it fails, it moves to `next_step_on_false`, or completes the enrollment if that's empty.

```php
$workflow->steps()->create([
    'step_number' => 1,
    'type' => WorkflowStepType::Condition,
    'config' => ['property' => 'plan', 'operator' => '=', 'value' => 'pro'],
    'next_step_on_true' => 2,
]);
```

### Outbound webhook step

A `webhook` step sends this JSON with a five-second timeout:

```json
{
    "workflow_id": 3,
    "workflow_name": "Demo request nurture",
    "contact": {
        "id": 17,
        "email": "ada@example.com",
        "first_name": "Ada",
        "last_name": null,
        "lead_score": 15,
        "lifecycle_stage": "lead"
    },
    "timestamp": "2026-10-02T16:45:00+00:00"
}
```

Headers:

- `X-Focal-Signature`: HMAC-SHA256 of the JSON body, keyed with `config.secret`, or with `app.key` when no secret is set. Set a secret, so receivers don't need your app key to verify requests.
- `X-Focal-Workflow-ID`: the workflow id.
- `User-Agent`: `Focal-RevOps-Webhook/1.0`.
- Anything in `config.headers`, which can override the above.

To verify a request on the receiving side:

```php
$expected = hash_hmac('sha256', $request->getContent(), 'shared-secret');

abort_unless(hash_equals($expected, (string) $request->header('X-Focal-Signature')), 401);
```

A non-2xx response or an exception is logged with status `failed` and the response code (500 for exceptions). The workflow continues either way; there are no retries. The request runs synchronously in the process that executes the step.

## Triggers

`Focal\Marketing\Enums\WorkflowTriggerType` describes what enrolls contacts. Only some triggers are wired up:

| Case | Value | Enrolls automatically when | `trigger_config` filter |
| --- | --- | --- | --- |
| `FormSubmitted` | `form_submitted` | A [form submission](forms-and-landing-pages.md#what-happens-on-submission) resolves a contact, or an [external lead](inbound-webhooks.md#external-lead-webhook) is ingested | `form_id` (ignored for external leads) |
| `AssetDownloaded` | `asset_downloaded` | A contact downloads a [gated asset](events-and-assets.md#gated-assets) through a signed link | `asset_id` |
| `EventAttended` | `event_attended` | A registration is [marked attended](events-and-assets.md#attendance-webhook) | `event_id` |
| `CustomEvent` | `custom_event` | A [custom behavioral event](inbound-webhooks.md#custom-behavioral-events) is tracked for a contact | `event_name` (case-insensitive) |
| `ContactCreated` | `contact_created` | Never | |
| `ListJoined` | `list_joined` | Never | |
| `LeadScoreReached` | `lead_score_reached` | Never | |
| `InboundWebhook` | `inbound_webhook` | Never (but see [the enrollment webhook](#enrollment-webhook)) | |
| `Manual` | `manual` | Never | |

A trigger without its filter key matches every form, asset, event, or event name. Only active workflows are triggered. For the triggers marked "Never", enroll contacts yourself.

## Enrolling contacts in code

```php
use Focal\Marketing\Actions\EnrollContactInWorkflowAction;

$enrollment = app(EnrollContactInWorkflowAction::class)->execute($workflow, $contact);
```

`execute(MarketingWorkflow $workflow, Contact $contact): ?WorkflowEnrollment`:

- returns `null` if the workflow is inactive or has no steps;
- returns the existing enrollment if the contact already has an `active` one in this workflow;
- otherwise creates an `active` enrollment at the lowest-numbered step, increments `enrollments_count`, and runs steps immediately until a delay or the end.

A contact whose previous enrollment completed is enrolled again. Because steps run synchronously, the request or job that enrolls the contact also performs the steps up to the first delay, including any webhook calls. When the last step finishes, the enrollment is set to `completed` and the workflow's `completed_count` is incremented.

The action also has `triggerFormWorkflows(MarketingForm $form, Contact $contact)` and `triggerCustomEventWorkflows(string $eventName, Contact $contact)`, which enroll the contact in every matching workflow.

## Processing delays

Schedule the command so that enrollments resume after their delays (see [scheduling](../installation.md#schedule-the-commands)):

```bash
php artisan marketing:process-workflows
```

It loads every `active` enrollment with a `next_run_at` in the past and runs its current step (and the steps after it, up to the next delay). It prints the number of enrollments advanced. `Focal\Marketing\Actions\ProcessDueWorkflowsAction::execute(): int` does the same from code.

A delay is only as precise as your schedule: with the command every five minutes, a 60-minute delay resumes 60 to 65 minutes later.

## Enrollment webhook

External systems (Zapier, Segment, Stripe, your own product) can enroll a contact over HTTP.

| Method | URI | Route name | Auth |
| --- | --- | --- | --- |
| `POST` | `/api/marketing/workflows/{workflow}/enroll` | `focal.marketing.workflows.enroll-webhook` | `FOCAL_MARKETING_API_TOKEN`, rate limited by `focal-api`. CSRF exempt. |

`{workflow}` is the workflow's id or its exact `name` (URL-encoded). The workflow's trigger type doesn't matter. Send the token as described in [API tokens](../configuration.md#api-tokens).

```bash
curl -X POST https://your-app.test/api/marketing/workflows/7/enroll \
  -H "Authorization: Bearer $FOCAL_MARKETING_API_TOKEN" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email": "trial@example.com", "first_name": "Tess", "company": "Initech", "trigger_event": "stripe.trial_started"}'
```

| Field | Rules |
| --- | --- |
| `email` | Required, valid email, max 255. Lowercased. |
| `first_name`, `last_name`, `company` | Optional strings, max 255. |
| `phone` | Optional string, max 50. |
| `trigger_event` | Optional string, max 100. Used in the timeline entry; defaults to `inbound_webhook`. |
| `properties` | Optional object of custom properties. See the note below. |

The endpoint loads or creates the contact (new contacts are `lead` / `new`), fills empty name and phone fields, associates the company with that exact name (creating it if needed), logs a `Webhook Enrollment: {workflow}` task, and enrolls the contact. It returns `201`:

```json
{
    "success": true,
    "workflow_id": 7,
    "workflow_name": "Trial onboarding",
    "contact_id": 52,
    "enrollment_id": 88,
    "status": "active",
    "message": "Contact successfully enrolled in workflow."
}
```

`status` is the enrollment status after the first steps ran, so it's `completed` for a workflow without delays. If the workflow has no steps, `enrollment_id` is `null`, `status` is `pending_steps`, and `message` is `Contact registered; workflow has no defined steps yet.` An unknown workflow returns `404` and an inactive one `422`, both with `success: false` and a `message`.

> Scalar values in `properties` are set on the contact but never saved, so they're currently lost. Send custom properties through the [external lead webhook](inbound-webhooks.md#external-lead-webhook) instead, or set them in your own code.

## Journey view

The package ships a `focal-marketing::workflow-journey` Blade view that renders a workflow's stats and steps. It expects a `$workflow` variable and is intended for admin panels such as the Filament plugin.
