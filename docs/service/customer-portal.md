---
title: Support portal and CSAT
description: The public ticket form, token-based ticket pages where customers follow and reply to tickets, and the CSAT satisfaction survey.
---

The support portal lets customers submit tickets, follow the conversation, reply, and rate their experience without an account. Each ticket's pages are addressed by its 40-character `portal_token`; whoever has the link can read the public thread and reply.

## Routes

These routes are in the `web` route group (`focal-service.routes.web`), with no prefix by default. See [Configuration reference](configuration.md#routes).

| Method | URI | Route name | Rate limit |
| :--- | :--- | :--- | :--- |
| GET | `/support` | `focal.support.create` | |
| POST | `/support` | `focal.support.store` | `focal-public` |
| GET | `/support/tickets/{token}` | `focal.support.show` | |
| POST | `/support/tickets/{token}/reply` | `focal.support.reply` | `focal-public` |
| GET | `/support/rate/{token}` | `focal.support.rate` | |
| POST | `/support/rate/{token}` | `focal.support.submitRating` | `focal-public` |

The POST routes use the web group's normal CSRF protection; the bundled forms include `@csrf`. An unknown token returns `404`.

Link to the form from your site with the route name, so a changed prefix or domain is picked up:

```blade
<a href="{{ route('focal.support.create') }}">Contact support</a>
```

## Submitting a ticket

`GET /support` shows a form. `POST /support` validates:

| Field | Rules |
| :--- | :--- |
| `name` | required, string, max 255 |
| `email` | required, email, max 255 |
| `subject` | required, string, max 255 |
| `priority` | required, one of `low`, `medium`, `high`, `urgent` |
| `description` | required, string |

The customer chooses the priority, and it sets the ticket's SLA targets. The controller then:

1. Finds the Core `Contact` with that email, ignoring case and surrounding whitespace (so `Dana@Example.com` finds `dana@example.com`), or creates one with the email lowercased and trimmed, splitting `name` into first and last name at the first space.
2. Creates the ticket with [`CreateTicketAction`](tickets.md#creating-tickets) and source `WebPortal`. The ticket is routed, a task is logged on the contact's timeline, and `TicketCreatedNotification` is queued for the customer.
3. Redirects to the ticket's page with the flash message "Your support ticket has been received. Our team will review it shortly." under the `status` session key.

While the customer types a subject, the form calls the [knowledge suggestion endpoint](knowledge-base.md#suggestion-api) and offers matching articles. Article titles, categories, and excerpts are inserted as text, so HTML in an article is shown as typed. If the customer clicks "This solved it", the form records a [deflection](knowledge-base.md#recording-deflections) and disables the ticket form.

## The ticket page

`GET /support/tickets/{token}` shows the ticket number, status, priority, contact name, owner name, creation date, channel, and every message that is not an internal note. `getPortalUrl()` on the ticket returns this URL, and every customer email links to it.

`POST /support/tickets/{token}/reply` takes a required `body` and adds it as a `Customer` message from the ticket's contact through [`ReplyTicketAction`](tickets.md#replying-and-internal-notes), so the status changes as described in [Statuses](tickets.md#statuses): a reply reopens a `Resolved` or `Closed` ticket unless `focal-service.reopen_on_customer_reply` is `false`. It redirects back with the flash message "Your reply has been posted to the ticket." If the ticket was [merged](routing.md#merging-tickets) into another, what happens depends on where the ticket came from, see [Replies to merged tickets](routing.md#replies-to-merged-tickets): for a ticket created from an email, by phone, or through the API, the reply is posted on the primary ticket and the flash message says so instead. For a ticket created on the portal or in the chat widget, the reply is refused with a `body` validation error asking the customer to reply to the latest email from your team. The page itself always shows the token's own ticket, never the primary. The bundled page hides the reply form once the ticket is `Closed` (merged tickets are closed), but the endpoint still accepts replies, so customers can reopen a closed ticket by email or from your own view.

## CSAT surveys

When a ticket is resolved with `ResolveTicketAction`, the customer receives `TicketResolvedCsatNotification` with a "Rate Your Support Experience" button that links to `getCsatUrl()`. The ticket page also shows a rating button once the ticket is `Resolved` or `Closed` and has no rating yet.

`GET /support/rate/{token}` shows a 1 to 5 star form with an optional comment. `POST /support/rate/{token}` validates:

| Field | Rules |
| :--- | :--- |
| `rating` | required, integer, 1 to 5 |
| `comment` | optional, string, max 1000 |

It saves `csat_rating` and `csat_comment` on the ticket and redirects to the ticket page with "Thank you! Your feedback has been recorded." The survey accepts a rating at any status, and a new submission replaces the previous one.

A rating of 1 or 2 also triggers service recovery:

- An internal `System` note is added to the ticket with the rating, the comment, and "Supervisor review recommended."
- If the ticket has a contact, a pending task titled `CSAT Service Recovery: Ticket #{number} ({rating}/5 stars)` is logged on the contact's timeline, due in 24 hours.

You can also record a rating from your own code with `ResolveTicketAction`'s `$csatRating` and `$csatComment` arguments. That path doesn't trigger service recovery.

## Customizing the pages

The pages are Blade views in the `focal-service` namespace: `focal-service::portal.create`, `focal-service::portal.show`, and `focal-service::portal.rate`. They are standalone HTML pages that load Tailwind CSS from `cdn.tailwindcss.com` and the Plus Jakarta Sans font from Google Fonts.

The package has no publish tag for its views. To change one, copy it from `vendor/focalcrm/service/resources/views` into `resources/views/vendor/focal-service` in your app, keeping the same relative path (for example `resources/views/vendor/focal-service/portal/show.blade.php`). Laravel uses your copy instead of the package's.

To replace the routes and controllers entirely, see [Using your own routes](configuration.md#using-your-own-routes).
