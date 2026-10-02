---
title: Tickets and conversations
description: The Ticket and TicketMessage models, ticket statuses, and how to create, reply to, resolve, close, and reopen tickets.
---

A ticket is a support request from a customer. Its conversation is a list of `TicketMessage` records: customer messages, agent replies, system messages, and internal notes. This page covers the models and the actions that change them.

## The ticket model

`Focal\Service\Models\Ticket` stores:

| Attribute | Type | Notes |
| :--- | :--- | :--- |
| `ticket_number` | string | Generated on create, see [Ticket numbers](#ticket-numbers-and-portal-tokens). Unique. |
| `portal_token` | string | 40-character random token for the customer's ticket page and chat session. Unique. |
| `subject`, `description` | string | `description` is optional. |
| `status` | `TicketStatus` | Defaults to `new`. |
| `priority` | `TicketPriority` | Defaults to `medium`. |
| `source` | `TicketSource` | Defaults to `web_portal`. |
| `contact_id`, `company_id` | int, nullable | Core `Contact` and `Company`. |
| `owner_id` | int, nullable | The assigned user. |
| `sla_policy_id` | int, nullable | See [SLA policies](sla-policies.md). |
| `team_id` | int, nullable | Stored and indexed, but not used by the package. |
| `first_response_due_at`, `resolution_due_at` | datetime | SLA deadlines, set on create. |
| `first_responded_at`, `resolved_at`, `closed_at` | datetime | Lifecycle timestamps. |
| `is_sla_response_breached`, `is_sla_resolution_breached` | bool | SLA breach flags. |
| `merged_into_ticket_id`, `merged_at` | | Set when the ticket is merged into another, see [Merging](routing.md#merging-tickets). |
| `csat_rating`, `csat_comment` | int (1–5), text | Customer satisfaction feedback. |
| `properties` | array | Custom data through Core's `HasCustomProperties`. |

Relationships: `contact()`, `company()`, `owner()`, `slaPolicy()`, `mergedInto()`, `mergedTickets()`, and `messages()`. `messages()` is always ordered by `created_at` ascending.

The service provider also adds `tickets()` to Core's `Contact` and `Company` models:

```php
$contact->tickets()->where('status', 'open')->count();
$company->tickets;
```

### Ticket numbers and portal tokens

When a ticket is created without a `ticket_number`, one is generated as `{prefix}-{year}-{5 random uppercase letters or digits}`, for example `TICK-2026-7WBPJ`. The prefix comes from `focal-service.defaults.prefix` (default `TICK`). A random 40-character `portal_token` is generated the same way.

`getPortalUrl()` returns the customer's ticket page (`route('focal.support.show', $token)`), and `getCsatUrl()` returns the satisfaction survey (`route('focal.support.rate', $token)`). See [Support portal and CSAT](customer-portal.md).

### SLA deadlines on create

When a ticket is created without an `sla_policy_id`, the policy with `is_default = true` is attached if one exists. If the ticket then has a policy and no `first_response_due_at`, both deadlines are calculated from the policy's targets for the ticket's priority. Deadlines are not recalculated if you later change the priority or policy. See [SLA policies](sla-policies.md).

## Statuses

| Status | Set when |
| :--- | :--- |
| `New` | The ticket is created. |
| `Open` | A routing rule assigns a `New` ticket, a customer replies to a `New`, `WaitingOnCustomer`, or `Resolved` ticket, or you call `reopen()`. |
| `WaitingOnCustomer` | The first public agent reply is posted (unless the ticket is `Resolved` or `Closed`). |
| `WaitingOnAgent` | Defined for your own workflows. No package code path currently leaves a ticket in this status. |
| `Resolved` | You call `resolve()` or `ResolveTicketAction`. |
| `Closed` | You call `close()`, the ticket is merged into another, or `service:run-automations` closes it. |

These transitions happen inside `Ticket::addMessage()`, which every action and public endpoint uses to post messages:

- A public `Agent` message on a ticket with no `first_responded_at` sets `first_responded_at` to now, sets `is_sla_response_breached` to whether the response was late, and moves the status to `WaitingOnCustomer` unless the ticket is resolved or closed. Later agent replies don't change the status.
- A public `Customer` message moves `Resolved` tickets back to `Open` and clears `resolved_at`, and moves `New` and `WaitingOnCustomer` tickets to `Open`. A customer message on a `Closed` ticket doesn't reopen it.
- Internal notes and `System` messages never change the status.

These status updates are saved quietly (without model events).

## Creating tickets

Use `CreateTicketAction` for tickets from your own code, such as phone calls logged by an agent or an integration:

```php
use Focal\Core\Models\Contact;
use Focal\Service\Actions\CreateTicketAction;
use Focal\Service\Enums\TicketPriority;
use Focal\Service\Enums\TicketSource;

$contact = Contact::where('email', 'dana@example.com')->firstOrFail();

$ticket = app(CreateTicketAction::class)->execute(
    subject: 'CSV export times out',
    description: 'The export runs for a minute and then fails.',
    priority: TicketPriority::High,
    source: TicketSource::Api,
    contact: $contact,
    properties: ['plan' => 'enterprise'],
);
```

The full signature:

```php
public function execute(
    string $subject,
    ?string $description = null,
    TicketPriority $priority = TicketPriority::Medium,
    TicketSource $source = TicketSource::WebPortal,
    ?Contact $contact = null,
    ?Company $company = null,
    ?Model $owner = null,
    ?SlaPolicy $slaPolicy = null,
    array $properties = []
): Ticket
```

The action does more than insert a row:

1. If no `$company` is given and the contact is associated with a company, the contact's first company is used.
2. The ticket is created with status `New`. SLA deadlines are set as described above.
3. If `$description` is not empty, it is also added as the first `Customer` message in the thread.
4. If no `$owner` is given, [`RouteTicketAction`](routing.md#routing-rules) runs. A matching rule assigns an owner and moves the ticket to `Open`.
5. If there is a contact, a pending task titled `Support Ticket #{number}: {subject}` is logged on the contact's timeline, due at the first response deadline.
6. If the contact has an email address, `TicketCreatedNotification` is sent to them. See [Notifications](#notifications).

`Ticket::create()` also works, and still generates the number, token, and SLA deadlines, but skips steps 1 and 3 to 6.

## Replying and internal notes

`ReplyTicketAction` posts a message to the thread:

```php
use Focal\Service\Actions\ReplyTicketAction;

$reply = app(ReplyTicketAction::class);

$reply->execute(
    ticket: $ticket,
    body: 'Thanks, Dana. We can reproduce this and are working on a fix.',
    user: $agent,
);

$reply->execute(
    ticket: $ticket,
    body: 'Slow query in the export job, see the linked issue.',
    user: $agent,
    isInternalNote: true,
);
```

The full signature:

```php
public function execute(
    Ticket $ticket,
    string $body,
    MessageSenderType $senderType = MessageSenderType::Agent,
    ?Model $user = null,
    ?Contact $contact = null,
    bool $isInternalNote = false,
    ?array $attachments = null
): TicketMessage
```

For a public `Agent` reply on a ticket with a contact, the action also logs a note on the contact's timeline (the first 150 characters of the reply) and, if the contact has an email address, sends `TicketRepliedNotification`. Internal notes and customer messages send nothing.

`$attachments` is stored as a JSON array on the message as given. The package doesn't upload or serve files; store them yourself and save whatever references you need.

To record a customer message from your own code, pass `senderType: MessageSenderType::Customer` and `contact:`.

### Ticket::addMessage()

Both the action and the public endpoints call `Ticket::addMessage()`, which you can also call directly when you don't want the timeline note or email:

```php
public function addMessage(
    string $body,
    MessageSenderType $senderType = MessageSenderType::Agent,
    ?int $userId = null,
    ?int $contactId = null,
    bool $isInternalNote = false,
    ?array $attachments = null
): TicketMessage
```

### Messages

`TicketMessage` has `ticket_id`, `sender_type` (`MessageSenderType`), `user_id`, `contact_id`, `body`, `is_internal_note`, and `attachments`, with `ticket()`, `user()`, and `contact()` relationships. `senderName()` returns the user's `name`, else the contact's full name, else the sender type label (for example "System Automation").

Message bodies are plain text. The bundled portal pages escape them and convert line breaks.

## Resolving, closing, and reopening

```php
use Focal\Service\Actions\ResolveTicketAction;

app(ResolveTicketAction::class)->execute(
    $ticket,
    resolutionNote: 'Fixed in release 4.2.1.',
);
```

`ResolveTicketAction::execute(Ticket $ticket, ?string $resolutionNote = null, ?int $csatRating = null, ?string $csatComment = null): Ticket`:

- Calls `$ticket->resolve($resolutionNote)`. A non-empty note is posted as a public `Agent` message attributed to the authenticated user (if any). The ticket moves to `Resolved`, `resolved_at` is set, and `is_sla_resolution_breached` is set to whether it was resolved after `resolution_due_at`.
- If `$csatRating` is given, saves it with `$csatComment`. The value is not validated here.
- If there is a contact, logs a note on their timeline, and if they have an email address, sends `TicketResolvedCsatNotification`, which links to the CSAT survey.

The model methods on their own send no email:

| Method | Effect |
| :--- | :--- |
| `resolve(?string $resolutionNote = null)` | As above, without the timeline note or email. |
| `close()` | Status `Closed`, `closed_at` set to now. |
| `reopen()` | Status `Open`, `resolved_at` and `closed_at` cleared. |
| `isFirstResponseBreached()` | `true` if flagged, or if there is no response yet and the deadline has passed. |
| `isResolutionBreached()` | `true` if flagged, or if the ticket is unresolved and the deadline has passed. |

## Notifications

The package sends these notifications on the `mail` channel. None implements `ShouldQueue`, so they are sent during the request unless you configure otherwise. Customer notifications go to the Core `Contact`, which uses Laravel's `Notifiable` trait and its `email` attribute.

| Notification | Sent to | Sent by | Main link |
| :--- | :--- | :--- | :--- |
| `TicketCreatedNotification` | Contact | `CreateTicketAction` | `getPortalUrl()` |
| `TicketRepliedNotification` | Contact | `ReplyTicketAction` (public agent replies) | `getPortalUrl()` |
| `TicketResolvedCsatNotification` | Contact | `ResolveTicketAction` | `getCsatUrl()` |
| `SlaBreachAlertNotification` | Ticket owner | `CheckSlaBreachesAction` | `/admin/tickets/{id}/edit` |

Customer email subjects start with `[#{ticket_number}]` for the customer's reference. The three customer emails also set a `Message-ID` that contains the ticket's portal token, `<ticket.{portal_token}.{unique}@{host}>`, which the [email webhook](inbound-email.md#threading-replies) uses, along with the portal link, to thread replies from the ticket's contact back into the ticket. The ticket number alone doesn't thread a reply.

Tickets created by the [chat widget](chat-widget.md) don't go through `CreateTicketAction`, so they send no confirmation email.

## Automatic closing

`service:run-automations` runs `RunServiceAutomationsAction`, which:

1. Closes tickets in `WaitingOnCustomer` whose `updated_at` is 7 or more days ago. It first posts a public message, "Ticket automatically closed after 7 days without customer response.", with sender type `Agent` and no user. No email is sent.
2. Closes `Resolved` tickets whose `resolved_at` is 48 hours or more ago.

Both periods are fixed in code. The command prints a table of how many tickets each step closed. The package doesn't schedule it; see [Installation](../installation.md#schedule-the-commands).

```bash
php artisan service:run-automations
```
