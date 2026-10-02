---
title: Email to ticket
description: Turn inbound emails into tickets and thread replies with the token-protected inbound email webhook.
---

The inbound email webhook turns emails sent to your support address into tickets. Configure your email provider's inbound parsing (inbound routes or parse webhooks) to post each message to this endpoint. New conversations become tickets; replies that reference an existing ticket are added to its thread.

## Endpoint

| | |
| :--- | :--- |
| Method and URI | `POST /api/service/inbound-email` |
| Route name | `focal.service.inbound-email` |
| Authentication | Service API token (required) |
| Rate limit | `focal-api` (600 requests per minute per IP by default) |
| CSRF | Exempt |

The `/api/service` prefix comes from `focal-service.routes.api.prefix`. See [Configuration reference](configuration.md#routes).

## API token

The endpoint is protected by the service API token:

```env
FOCAL_SERVICE_API_TOKEN=a-long-random-string
```

Generate a value with `php -r 'echo bin2hex(random_bytes(32));'`. It is read from `focal-service.api.token`.

Until a token is set, the endpoint returns `403` and creates nothing. A missing or wrong token returns `401`. Send the token as `Authorization: Bearer <token>`, an `X-Focal-Token` header, or a `?token=` query parameter for providers that only let you enter a URL. See [API tokens](../configuration.md#api-tokens) for details shared by all Focal modules.

## Request

The webhook accepts JSON or form-encoded bodies and reads the first field that is present:

| Value | Fields read, in order |
| :--- | :--- |
| Sender | `from`, `sender`, `From` |
| Subject | `subject`, `Subject` (defaults to `No Subject`) |
| Body | `body`, `text`, `stripped-text`, `html`, `body_html` |
| Reply references | `In-Reply-To` or `References`, as HTTP headers or body fields |

The sender may be a bare address (`dana@example.com`) or a name and address (`Dana Scully <dana@example.com>`). With a bare address, the part before the `@` is used as the name.

Check these names against what your provider sends. Some providers use other field names (Postmark's JSON, for example, sends the body as `TextBody` and `HtmlBody`, which the webhook doesn't read), so you may need a small route in your app that maps the fields and forwards the request. Attachments are ignored. An HTML-only body is stored as the HTML source.

```bash
curl -X POST https://crm.example.com/api/service/inbound-email \
  -H "Authorization: Bearer $FOCAL_SERVICE_API_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"from": "Dana Scully <dana@example.com>", "subject": "Cannot log in", "text": "The sign-in page says my account is locked."}'
```

## Responses

A new ticket returns `201`:

```json
{
    "status": "created",
    "ticket_number": "TICK-2026-7WBPJ",
    "portal_url": "https://crm.example.com/support/tickets/3Ekp9wqpTbwiMh55MGZdv1aOUu3QOWG9kulLvRJf"
}
```

A reply added to an existing ticket returns `200`:

```json
{
    "status": "appended",
    "ticket_number": "TICK-2026-7WBPJ",
    "message_id": 42
}
```

A request without a sender or body returns `422`:

```json
{
    "error": "Missing required email fields (from, body/text)."
}
```

## New tickets

When the email doesn't reference an existing ticket, or references one whose contact isn't the sender (see [Threading replies](#threading-replies)):

1. The sender is matched to a Core `Contact` by exact email address. If none exists, a contact is created with the parsed first and last name (first name `Customer` if no name could be parsed).
2. A ticket is created through [`CreateTicketAction`](tickets.md#creating-tickets) with the subject, the body as description and first message, priority `Medium`, and source `Email`.

Because it uses `CreateTicketAction`, the ticket is routed, a task is logged on the contact's timeline, and the customer receives `TicketCreatedNotification`, whose subject starts with `[#TICK-2026-7WBPJ]`.

## Threading replies

Before creating a ticket, the webhook looks for an existing one, checking in this order:

1. The subject, for a ticket number such as `TICK-2026-7WBPJ` or `[#TICK-2026-7WBPJ]`.
2. The `In-Reply-To` header or field, then `References`, for a ticket number.
3. The body, for a 40-character portal token directly after `support/tickets/` (the [customer portal](customer-portal.md) link in ticket emails, `/support/tickets/{token}`), `portal/` or `token=`.
4. The body, for a ticket number.

Ticket numbers are matched case-insensitively against the configured prefix (`focal-service.defaults.prefix`) and the default `TICK`, followed by a four-digit year and four to six letters or digits. A match is looked up in the case the ticket was stored with: the configured prefix as written in config and the rest in upper case, so with a prefix of `Acme`, a reply quoting `acme-2026-7wbpj` finds `Acme-2026-7WBPJ`. Because every notification the package sends puts `[#{ticket_number}]` in the subject, a customer replying to one of those emails is threaded by the first check.

### Sender check

A referenced ticket is only used when the sender is that ticket's contact: the sender's email address must equal the contact's email address, compared case-insensitively after trimming whitespace. A ticket with no contact never matches. If a reference points at someone else's ticket, the webhook moves on to the next reference, and if none match, it creates a new ticket for the sender as described in [New tickets](#new-tickets), exactly as if the email had no ticket reference. Knowing a ticket number or portal link isn't enough to post on another customer's ticket.

When a ticket is found, the body is added as a `Customer` message from the ticket's contact through [`ReplyTicketAction`](tickets.md#replying-and-internal-notes). A customer message reopens a `Resolved` ticket and moves `New` and `WaitingOnCustomer` tickets to `Open`. A `Closed` ticket stays closed, including a ticket that was [merged](routing.md#merging-tickets) into another.

The `From` address is whatever your email provider reports, so the check is only as strong as your provider's sender verification. Enable SPF, DKIM and DMARC checks on your inbound route if your provider offers them, and keep treating inbound messages as customer input.
