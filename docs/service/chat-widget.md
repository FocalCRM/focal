---
title: Chat widget
description: Embed the support chat widget on any site, and use the chat JSON API that powers it.
---

The package includes `widget.js`, a dependency-free script that adds a chat launcher to any web page. A visitor enters their name, email, and question, which starts a ticket with source `Chat`. Agents answer with the normal ticket actions, and the widget polls for new messages.

## Embedding the widget

The script is at `resources/js/widget.js` in the package. It is not published or served by a route, so copy it into your public directory (or your asset pipeline):

```bash
mkdir -p public/js
cp vendor/focalcrm/service/resources/js/widget.js public/js/focal-chat-widget.js
```

Then add it to the pages where the launcher should appear. Set `window.FOCAL_CHAT_API_URL` to the full base URL of the chat API, including any configured prefix, before the script loads:

```html
<script>
    window.FOCAL_CHAT_API_URL = 'https://crm.example.com/api/service';
</script>
<script src="https://crm.example.com/js/focal-chat-widget.js" async></script>
```

Without `FOCAL_CHAT_API_URL`, the widget calls `/api/service` on the page's own origin. Re-copy the file when you update the package. Copies taken from earlier versions inserted the sender name into the page as HTML, so re-copy the script if yours predates this fix.

The widget:

- adds a launcher button in the bottom-right corner and a chat window, with inline styles;
- shows a start form with name, email, optional company, and message;
- stores the session token in `localStorage` under `focal_support_chat_token`, so a returning visitor sees the same conversation;
- polls for messages every 4 seconds while the window is open;
- renders each message's `sender_name`, `body`, and `created_at` as text (with `textContent`), so HTML in a visitor's name or message is shown as typed, never run;
- loads only once per page (`window.FocalChatWidgetLoaded`).

### Cross-origin use

When the widget runs on a different domain from your Laravel app, the browser sends CORS preflight requests. Laravel's `HandleCors` middleware answers them for the paths in `config/cors.php`, which by default include `api/*` and allow all origins, so the default `api/service` prefix works. If you change `FOCAL_SERVICE_API_PREFIX` to something outside `api/`, or restrict `allowed_origins`, update `config/cors.php` to match.

The chat endpoints are exempt from CSRF verification, so no token is needed. They still run the `web` middleware group by default (see [Configuration reference](configuration.md#routes)), which starts a session for each request.

## What starting a chat does

`POST /chat/start`:

1. Finds or creates a Core `Contact` by email (lowercased). New contacts get the name split at the first space and `lifecycle_stage` `customer`.
2. If `company` is given, finds or creates a Core `Company` with that exact name (new companies get `lifecycle_stage` `customer`) and associates the contact with it.
3. Creates a ticket with subject `Live Chat inquiry from {full name}`, source `Chat`, priority `Medium`, and the message as description. The default SLA policy applies.
4. Adds the visitor's message as a `Customer` message, which moves the ticket to `Open`, and a `System` welcome message: "Hi {first name}! Thanks for reaching out to support. A member of our team has received your message and will reply here momentarily." (with a wave emoji after the name).

Chat tickets don't go through `CreateTicketAction`, so they are not [routed](routing.md#routing-rules), no task is logged on the contact's timeline, and no confirmation email is sent. Route them yourself if you need an owner, for example from a model observer or a scheduled job that calls `RouteTicketAction` for unassigned `Chat` tickets.

The token returned is the ticket's `portal_token`, so the same conversation is also available at the [support portal](customer-portal.md#the-ticket-page) URL.

### Replying from your agent UI

Reply with [`ReplyTicketAction`](tickets.md#replying-and-internal-notes). The reply shows in the widget on its next poll. Because the chat contact has an email address, a public agent reply also emails them `TicketRepliedNotification`. Internal notes never appear in the widget.

## API reference

All three endpoints are in the `api` route group, under `/api/service` by default.

| Method | URI | Route name | Rate limit | CSRF |
| :--- | :--- | :--- | :--- | :--- |
| POST | `/api/service/chat/start` | `focal.service.chat.start` | `focal-public` | Exempt |
| POST | `/api/service/chat/{token}/message` | `focal.service.chat.message` | `focal-public` | Exempt |
| GET | `/api/service/chat/{token}/messages` | `focal.service.chat.messages` | None | n/a |

If you build your own client, send `Accept: application/json` so validation errors come back as `422` JSON instead of a redirect.

### Start a chat

```bash
curl -X POST https://crm.example.com/api/service/chat/start \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"name": "Dana Scully", "email": "dana@example.com", "company": "Acme", "message": "Is there an API rate limit?"}'
```

| Field | Rules |
| :--- | :--- |
| `name` | required, string, max 255 |
| `email` | required, email, max 255 |
| `message` | required, string |
| `company` | optional, string, max 255 |

Response, `201`:

```json
{
    "success": true,
    "token": "3Ekp9wqpTbwiMh55MGZdv1aOUu3QOWG9kulLvRJf",
    "ticket_number": "TICK-2026-7WBPJ",
    "messages": [
        {
            "id": 1,
            "sender_type": "customer",
            "sender_name": "Dana Scully",
            "body": "Is there an API rate limit?",
            "is_customer": true,
            "created_at": "0 seconds ago"
        },
        {
            "id": 2,
            "sender_type": "system",
            "sender_name": "System Automation",
            "body": "Hi Dana! 👋 Thanks for reaching out to support. A member of our team has received your message and will reply here momentarily.",
            "is_customer": false,
            "created_at": "0 seconds ago"
        }
    ]
}
```

Each message has `id`, `sender_type` (`customer`, `agent`, or `system`), `sender_name` (see `TicketMessage::senderName()`), `body`, `is_customer`, and `created_at` as a relative time string such as `"5 minutes ago"`. Internal notes are never included.

### Send a message

```bash
curl -X POST https://crm.example.com/api/service/chat/3Ekp9wqpTbwiMh55MGZdv1aOUu3QOWG9kulLvRJf/message \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"message": "Thanks, that helps."}'
```

`message` is required. The message is added as a `Customer` message, which moves a `New`, `WaitingOnCustomer`, or `Resolved` ticket to `Open`. The response is `{"success": true, "messages": [...]}` with the full public thread. An unknown token returns `404` with `{"error": "Chat session not found."}`.

### Fetch messages

```bash
curl https://crm.example.com/api/service/chat/3Ekp9wqpTbwiMh55MGZdv1aOUu3QOWG9kulLvRJf/messages
```

Response:

```json
{
    "ticket_number": "TICK-2026-7WBPJ",
    "status": "open",
    "messages": []
}
```

`messages` has the same shape as above. An unknown token returns `404` with `{"error": "Chat session not found."}`. This endpoint has no rate limit, since the widget polls it.
