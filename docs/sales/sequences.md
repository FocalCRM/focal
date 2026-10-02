---
title: Sequences, templates, and playbooks
description: Run multi-step outbound cadences, render email templates with merge tags, and capture qualification answers with playbooks.
---

Sequences (also called cadences) take a contact through a series of timed steps: emails, calls, LinkedIn touches, and tasks. Email templates provide the content for email steps. Playbooks are qualification scripts whose answers are saved onto a deal or contact.

## Sequences

A `Focal\Sales\Models\SalesSequence` has a `name`, an optional `description`, `is_active` (defaults to `true`), an optional `user_id` (the author), and a `steps` array. Each step is an array with:

| Key | Notes |
| --- | --- |
| `step` | The step number, for your reference. Steps run in array order. |
| `type` | `email`, `call`, `linkedin`, or anything else (treated as a task). |
| `delay_days` | Days to wait before this step. |
| `title` | Used in the activity title. |
| `template_id` | Optional. A `SalesEmailTemplate` ID for `email` steps. |

```php
use Focal\Sales\Models\SalesSequence;

$sequence = SalesSequence::create([
    'name' => 'Outbound Q3',
    'user_id' => $rep->id,
    'steps' => [
        ['step' => 1, 'type' => 'email', 'delay_days' => 0, 'title' => 'Intro email', 'template_id' => $template->id],
        ['step' => 2, 'type' => 'call', 'delay_days' => 2, 'title' => 'Intro call'],
        ['step' => 3, 'type' => 'email', 'delay_days' => 3, 'title' => 'Break-up email'],
    ],
]);

$sequence->totalSteps(); // 3
```

The first step's `delay_days` counts from enrollment. Each later step's `delay_days` counts from the day the previous step was completed. Due dates are whole dates, not times.

## Enrolling contacts

```php
use Focal\Sales\Actions\EnrollContactInSequenceAction;

$enrollment = app(EnrollContactInSequenceAction::class)->execute($contact, $sequence, $rep->id);
```

`execute(Contact $contact, SalesSequence $sequence, int|string|null $enrolledById = null)` creates or updates the `SalesSequenceEnrollment` for that contact and sequence. Enrolling the same contact again restarts it at step 1. The enrollment gets:

- `current_step = 1` and `status = 'active'`,
- `next_step_due_at` = today plus the first step's `delay_days`,
- `enrolled_by_id` = the given ID or the authenticated user. Activities created by the sequence use this as their creator.

If the contact's `lead_status` is `New`, it is changed to `InProgress` (quietly, without model events).

`$contact->salesSequenceEnrollments` lists a contact's enrollments, newest first. `$sequence->enrollments` lists a sequence's.

### Enrollment status

`status` is a plain string:

- `active`: still running.
- `completed`: every step has been done.
- `unenrolled`: removed early.

Contacts are unenrolled automatically when:

- an associated deal is won or lost ([Deals](deals.md#moving-between-stages)),
- they book a meeting through a [meeting link](meeting-links.md),
- their `lead_status` is `Unqualified` or `BadTiming` when the next step is processed.

To remove a contact yourself, update the enrollment's `status` to `unenrolled`.

## Processing due steps

Steps are only executed by `Focal\Sales\Actions\ProcessCadencesAction`, usually through the Artisan command:

```bash
php artisan sales:process-cadences
```

The command prints a table of counts. The package does not schedule it; see [Scheduling](configuration.md#scheduling).

The action picks up `active` enrollments whose `next_step_due_at` is today or earlier (or empty), skipping enrollments in inactive sequences. For each one:

**Email steps** log a completed `email` activity on the contact, mark the contact as contacted, change a `New` lead status to `InProgress`, and advance to the next step. If the step has a `template_id`, the subject and body come from the rendered template; otherwise the subject is `Outbound: {title}`. **No email is sent.** The activity records the touch; to deliver mail, listen for the activity being created or send it from your own job.

**Call, LinkedIn, and task steps** create a pending `call`, `linkedin`, or `task` activity on the contact, due now, titled `{type label}: {title}`. The enrollment then waits. On each later run, once the rep has marked that activity as completed, the contact is marked as contacted and the enrollment advances. While the activity is still pending nothing happens.

After the last step the enrollment is set to `completed`.

`execute()` returns counts:

```php
use Focal\Sales\Actions\ProcessCadencesAction;

$stats = app(ProcessCadencesAction::class)->execute();
// ['processed' => 1, 'emails_sent' => 1, 'tasks_created' => 0, 'unenrolled' => 0, 'completed' => 0]
```

`emails_sent` counts email activities logged, not emails delivered.

## Email templates

A `Focal\Sales\Models\SalesEmailTemplate` has `name`, `subject`, `body_html`, `category` (defaults to `general`), `user_id`, and `is_shared` (defaults to `true`).

### Rendering with CRM context

`renderWithContext(?Contact $contact = null, ?Deal $deal = null, ?Model $user = null, array $extra = [])` returns `['subject' => ..., 'body_html' => ...]` with merge tags replaced. Sequence email steps call it with the contact only.

```php
use Focal\Sales\Models\SalesEmailTemplate;

$template = SalesEmailTemplate::create([
    'name' => 'Intro',
    'subject' => 'Quick question, {{ contact.first_name }}',
    'body_html' => '<p>Hi {{ contact.first_name }}, I work with teams like {{ company.name }}.</p>',
]);

$rendered = $template->renderWithContext(contact: $contact, deal: $deal, user: $rep);

$rendered['subject'];   // "Quick question, Dana"
$rendered['body_html'];
```

Tags use `{{ path }}` with dot notation, with or without spaces. Available paths:

| Prefix | Keys |
| --- | --- |
| `contact.` | `id`, `first_name`, `last_name`, `name`, `full_name`, `email`, `phone`, `job_title`, `title`, `timezone`, `lead_status` (label) |
| `company.` | `id`, `name`, `domain`, `industry` (from the contact's first company) |
| `deal.` | `id`, `name`, `amount`, `currency`, `formatted_amount`, `stage`, `expected_close_date`, `days_in_stage` |
| `user.`, `sender.`, `rep.`, `owner.` | `id`, `name`, `email` (all four refer to the `$user` argument) |

Custom properties resolve too: `{{ contact.renewal_tier }}` reads the contact's `renewal_tier` property when there is no built-in key of that name. The same works for `company.` and `deal.`. Keys in `$extra` are merged in at the top level, so `['offer' => ['code' => 'Q3']]` makes `{{ offer.code }}` available.

Tags that don't resolve become an empty string. `deal.formatted_amount` always uses a `$` sign, whatever the deal's currency.

In `body_html`, merge values are HTML-escaped with Laravel's `e()`, so a contact named `<b>Dana</b>` or a company called `R&D Labs` appears as typed (`&lt;b&gt;Dana&lt;/b&gt;`, `R&amp;D Labs`) and can't inject markup. The subject is plain text, so values go into it unescaped; escape the subject yourself if you put it into HTML. Write the HTML you want in the template itself, not in merge values. Escaping happens once, when the template is rendered, so don't escape values before passing them in `$extra` or they will be escaped twice.

The parser is `Focal\Sales\Services\TemplateParser`, with `parse(string $template, array $context = [])` for plain text (values inserted as-is), `parseHtml(string $template, array $context = [])` for HTML (values escaped with `e()`), and `buildContext(?Contact, ?Deal, ?Model $user, array $extra)` if you want to use it on other strings.

### Simple replacement

`render(array $variables = [])` replaces `{{ key }}` and `{{key}}` with the given strings, without CRM context. As with `renderWithContext()`, values are HTML-escaped in `body_html` and inserted as-is in the subject:

```php
$template->render(['first_name' => 'Sam']);
```

## Playbooks

A `Focal\Sales\Models\SalesPlaybook` is a list of questions. Answers are written to custom properties on a deal or contact and summarized in a note.

| Attribute | Notes |
| --- | --- |
| `name` | |
| `slug` | Unique. |
| `category` | Defaults to `qualification`. |
| `framework` | Defaults to `custom`. The presets use `bant` and `meddic`. |
| `description` | |
| `questions` | List of `['id', 'label', 'type', 'options'?, 'target_property'?, 'help'?]`. |
| `is_active` | Defaults to `true`. |
| `user_id` | The author. |

`type` and `options` describe how a UI should ask the question; the package does not validate answers against them.

Two presets return ready-to-create attribute arrays: `SalesPlaybook::defaultBantPreset()` (slug `bant-qualification`) and `SalesPlaybook::defaultMeddicPreset()` (slug `meddic-enterprise`).

```php
use Focal\Sales\Actions\ExecuteSalesPlaybookAction;
use Focal\Sales\Models\SalesPlaybook;

$playbook = SalesPlaybook::create(SalesPlaybook::defaultBantPreset());

app(ExecuteSalesPlaybookAction::class)->execute(
    target: $deal,
    playbook: $playbook,
    answers: [
        'budget_status' => 'Allocated & Approved',
        'target_timeline' => 'This Quarter (1-3 months)',
    ],
    userId: $rep->id,
);

$deal->getProperty('qualification_budget'); // "Allocated & Approved"
```

`execute(Deal|Contact $target, SalesPlaybook $playbook, array $answers, int|string|null $userId = null)` is keyed by question `id`. For each question with a non-empty answer it sets the question's `target_property` (if any) on the target, then saves the target once. It logs a note activity titled `Playbook: {name}` with a Markdown summary of the answered questions. Answers for unknown question IDs are ignored.
