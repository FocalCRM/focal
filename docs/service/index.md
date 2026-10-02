---
title: Service
description: What the focalcrm/service help desk module adds on top of Core, its models, and where to read next.
---

`focalcrm/service` is Focal's help desk module. It adds support tickets with threaded conversations, SLA policies with business hours, automatic ticket routing, canned responses, ticket merging, a public knowledge base, a token-based customer portal with CSAT surveys, an embeddable chat widget, and an email-to-ticket webhook.

The package is headless: it ships models, actions, Artisan commands, notifications, and a small set of public Blade pages and JSON endpoints for customers. It has no agent-facing UI of its own. You build agent screens in your application (or use the Filament admin, see [Installation](../installation.md)) and call the package's actions from them.

## Requirements

- PHP 8.3 or later
- Laravel 12 or 13
- `focalcrm/core`, which provides the `Contact` and `Company` models, the activity timeline, and the shared route, token, and rate limit helpers. See [Core](../core/index.md).

```bash
composer require focalcrm/service
php artisan migrate
```

The service provider, `Focal\Service\ServiceHubServiceProvider`, is auto-discovered. It loads the package migrations, registers the public routes, the `focal-service::` view namespace, and two Artisan commands. See [Installation](../installation.md) for publishing config and migrations and for scheduling the commands.

## What it adds to Core

Service works on Core's records rather than defining its own customers:

- Every ticket can belong to a Core `Contact` and `Company`. The service provider adds a `tickets()` relationship to both models at runtime, so `$contact->tickets` and `$company->tickets` work without changes to Core.
- When Core [merges](../core/duplicates-and-merging.md) two contacts or two companies, Service moves the duplicate's tickets (soft-deleted ones included) to the surviving record, and on a contact merge its ticket messages too.
- Ticket owners, message authors, and article authors are your application's user model, resolved through Core's user model setting (see [Configuration](../configuration.md#the-user-model)).
- Creating, routing, replying to, and resolving tickets writes notes and tasks to the contact's activity timeline.
- Customers who submit a ticket, start a chat, or email support are matched to an existing contact by email address, or a new contact is created.
- `Ticket` uses Core's `HasCustomProperties` trait, so you can store extra data in its `properties` column with `getProperty()`, `setProperty()`, and the `whereProperty()` scope.

## Models

All models are in the `Focal\Service\Models` namespace. Table names come from `focal-service.tables` (see [Configuration reference](configuration.md#tables)).

| Model | Default table | Purpose |
| :--- | :--- | :--- |
| `Ticket` | `focal_service_tickets` | A support request: number, subject, status, priority, source, contact, company, owner, SLA deadlines, CSAT rating, portal token. Soft deletes. |
| `TicketMessage` | `focal_service_ticket_messages` | One entry in a ticket's thread: a customer message, agent reply, system message, or internal note. |
| `SlaPolicy` | `focal_service_sla_policies` | First response and resolution targets per priority, with optional business hours and holidays. |
| `TicketRoutingRule` | `focal_service_routing_rules` | Criteria plus a pool of users for round-robin assignment. |
| `CannedResponse` | `focal_service_canned_responses` | A reusable reply with a title, shortcut, and category. |
| `KnowledgeArticle` | `focal_service_articles` | A help center article with view, vote, and deflection counters. |

## Enums

All enums are string-backed and live in `Focal\Service\Enums`.

| Enum | Cases (value) |
| :--- | :--- |
| `TicketStatus` | `New` (`new`), `Open` (`open`), `WaitingOnCustomer` (`waiting_on_customer`), `WaitingOnAgent` (`waiting_on_agent`), `Resolved` (`resolved`), `Closed` (`closed`) |
| `TicketPriority` | `Low` (`low`), `Medium` (`medium`), `High` (`high`), `Urgent` (`urgent`) |
| `TicketSource` | `WebPortal` (`web_portal`), `Email` (`email`), `Phone` (`phone`), `Chat` (`chat`), `Api` (`api`) |
| `MessageSenderType` | `Agent` (`agent`), `Customer` (`customer`), `System` (`system`) |

Each enum has a `label()` method. `TicketStatus` and `TicketPriority` also have `color()` (a Filament-style color name), and `TicketPriority` has `weight()` (1 for `Low` to 4 for `Urgent`). `TicketStatus::isClosed()` returns `true` for `Resolved` and `Closed`.

## Actions

Business logic lives in action classes in `Focal\Service\Actions`. Resolve them from the container with `app()` or inject them.

| Action | What it does |
| :--- | :--- |
| `CreateTicketAction` | Creates a ticket, seeds the first message, routes it, logs to the contact timeline, and emails the customer. |
| `ReplyTicketAction` | Adds a reply or internal note and emails the customer on public agent replies. |
| `ResolveTicketAction` | Resolves a ticket and sends the CSAT survey email. |
| `RouteTicketAction` | Assigns an owner using the active routing rules. |
| `MergeTicketsAction` | Moves a duplicate ticket's messages into a primary ticket and closes the duplicate. |
| `CheckSlaBreachesAction` | Flags overdue tickets, alerts owners, and escalates unassigned tickets. |
| `RunServiceAutomationsAction` | Closes stale and long-resolved tickets. |
| `DeflectTicketAction` | Finds knowledge base articles matching a customer's question. |

## Artisan commands

| Command | Purpose |
| :--- | :--- |
| `service:check-sla` | Runs `CheckSlaBreachesAction`. See [SLA policies](sla-policies.md#checking-for-breaches). |
| `service:run-automations` | Runs `RunServiceAutomationsAction`. See [Tickets](tickets.md#automatic-closing). |

Neither command is scheduled by the package. Add both to your scheduler as described in [Installation](../installation.md#schedule-the-commands).

## Pages in this section

- [Tickets and conversations](tickets.md): the ticket model, statuses, creating and replying to tickets, notifications, and automatic closing.
- [SLA policies and business hours](sla-policies.md): due dates, business-hours calculation, breach checks, and alerts.
- [Routing, canned responses, and merging](routing.md): tools for agents working the queue.
- [Email to ticket](inbound-email.md): the inbound email webhook and its API token.
- [Support portal and CSAT](customer-portal.md): the public ticket form, token-based ticket pages, and satisfaction surveys.
- [Chat widget](chat-widget.md): embedding the messenger and its JSON API.
- [Knowledge base and deflection](knowledge-base.md): articles, the help center, and article suggestions.
- [Configuration reference](configuration.md): every config key, environment variable, route, and publish tag.
