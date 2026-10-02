---
title: Routing, canned responses, and merging
description: Assign new tickets with round-robin routing rules, store reusable replies, and merge duplicate tickets.
---

This page covers three tools for working the ticket queue: routing rules that pick an owner for new tickets, canned responses for common replies, and merging duplicate tickets into one.

## Routing rules

A `Focal\Service\Models\TicketRoutingRule` matches tickets by criteria and assigns them to a pool of users in turn.

| Attribute | Default | Notes |
| :--- | :--- | :--- |
| `name` | | Shown in the contact timeline note. |
| `is_active` | `true` | Inactive rules are skipped. |
| `sort_order` | `0` | Rules are tried in ascending order. |
| `criteria` | `null` | Array of conditions, all of which must match. Empty or `null` matches every ticket. |
| `assigned_user_ids` | | Array of user IDs. Rules with an empty pool are skipped. |
| `last_assigned_index` | `-1` | Round-robin position, updated on each assignment. |

Supported criteria keys:

| Key | Matches when |
| :--- | :--- |
| `priority` | The ticket's priority value equals it, for example `'urgent'`. |
| `source` | The ticket's source value equals it, for example `'email'`. |
| `keyword` | The subject or description contains it (case-insensitive). |
| `has_company` | `true`: the ticket has a company. `false`: it doesn't. |

Other keys are ignored.

```php
use Focal\Service\Models\TicketRoutingRule;

TicketRoutingRule::create([
    'name' => 'Urgent email',
    'sort_order' => 1,
    'criteria' => ['priority' => 'urgent', 'source' => 'email'],
    'assigned_user_ids' => [$alice->id],
]);

TicketRoutingRule::create([
    'name' => 'Billing',
    'sort_order' => 2,
    'criteria' => ['keyword' => 'invoice', 'has_company' => true],
    'assigned_user_ids' => [$bob->id, $carol->id],
]);

TicketRoutingRule::create([
    'name' => 'Everything else',
    'sort_order' => 99,
    'assigned_user_ids' => [$alice->id, $bob->id, $carol->id],
]);
```

### How routing runs

`CreateTicketAction` calls `RouteTicketAction` for every ticket created without an owner, which includes tickets from the [support portal](customer-portal.md) and the [email webhook](inbound-email.md). Tickets started from the [chat widget](chat-widget.md) are not routed. You can route any ticket yourself:

```php
use Focal\Service\Actions\RouteTicketAction;

$result = app(RouteTicketAction::class)->execute($ticket);

if ($result !== null) {
    $result['assigned_user_id']; // int
    $result['rule'];             // the TicketRoutingRule that matched
}
```

The first active rule (by `sort_order`) whose criteria match wins. The action then:

- picks the next user in that rule's pool after `last_assigned_index`, wrapping around, and saves the new index;
- sets the ticket's `owner_id`, and moves the status from `New` to `Open` (other statuses are left alone);
- logs a note on the contact's timeline: "Support Ticket #{number} auto-assigned to {name} via routing rule [{rule}]."

It returns `null` when no rule matches. Routing doesn't consider workload or whether an agent is available; keep the pools up to date.

## Canned responses

`Focal\Service\Models\CannedResponse` stores reusable replies:

| Attribute | Default | Notes |
| :--- | :--- | :--- |
| `title` | | |
| `shortcut` | | Unique, for example `/reset`. |
| `category` | `General` | |
| `content` | | The reply text. |
| `user_id` | `null` | The author, through the `user()` relationship. |
| `is_shared` | `true` | Whether other agents should see it. |

The package stores canned responses but doesn't render or filter them: there is no variable substitution, and `is_shared` is not enforced. Load the response in your agent UI and post its content with `ReplyTicketAction`:

```php
use Focal\Service\Actions\ReplyTicketAction;
use Focal\Service\Models\CannedResponse;

CannedResponse::create([
    'title' => 'Password reset steps',
    'shortcut' => '/reset',
    'category' => 'Accounts',
    'content' => 'You can reset your password from the sign-in page using "Forgot password".',
    'user_id' => $agent->id,
]);

$canned = CannedResponse::where('shortcut', '/reset')->firstOrFail();

app(ReplyTicketAction::class)->execute(
    ticket: $ticket,
    body: $canned->content,
    user: $agent,
);
```

## Merging tickets

When a customer opens the same issue twice, merge the duplicate into the ticket you want to keep:

```php
use Focal\Service\Actions\MergeTicketsAction;

$primary = app(MergeTicketsAction::class)->execute(
    primaryTicket: $primaryTicket,
    secondaryTicket: $duplicateTicket,
    reason: 'Same customer, same issue',
    performedByUserId: $request->user()->id,
);
```

`MergeTicketsAction::execute(Ticket $primaryTicket, Ticket $secondaryTicket, ?string $reason = null, ?int $performedByUserId = null): Ticket` runs in a database transaction and:

1. Moves every message from the secondary ticket to the primary. Messages keep their timestamps, so the primary thread shows both conversations in time order.
2. Adds an internal `System` note to the primary: "Ticket #{number} ('{subject}') was merged into this ticket." plus the reason if given.
3. Sets the secondary ticket's `merged_into_ticket_id` and `merged_at`, and closes it (`Closed`, `closed_at` set).
4. Adds an internal `System` note to the secondary ticket saying it was merged into the primary.

It returns a fresh copy of the primary ticket. Merging a ticket into itself throws an `InvalidArgumentException`. The secondary ticket's SLA fields, CSAT, owner, and contact are not copied.

Use `$ticket->mergedInto` and `$ticket->mergedTickets` to navigate merges.

The secondary ticket keeps its number and portal token. Email replies that carry its portal token (its portal link, or the Message-ID of an email sent about it) are still added to the secondary (closed) ticket, not the primary, and do not reopen it. See [Email to ticket](inbound-email.md#threading-replies).
