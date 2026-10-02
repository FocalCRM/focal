---
title: Meeting links
description: Give reps public booking pages that create contacts and meeting activities.
---

A meeting link is a public page at `/meet/{slug}` where a prospect picks a date and time to meet a rep. Booking creates or updates the contact, logs a pending meeting activity owned by the rep, and stops any active sequences for that contact.

## Creating a link

`Focal\Sales\Models\SalesMeetingLink`:

| Attribute | Type | Notes |
| --- | --- | --- |
| `user_id` | int | The rep. Deleting the user deletes their links. |
| `slug` | string | Unique; used in the URL. |
| `title` | string | Defaults to `30 Min Meeting`. |
| `duration_minutes` | int | Defaults to `30`. Stored in the meeting activity's metadata. |
| `description` | text, nullable | Shown on the booking page. |
| `working_hours` | array, nullable | Stored but not used by the package. |
| `is_active` | bool | Defaults to `true`. Inactive links return 404. |

```php
use Focal\Sales\Models\SalesMeetingLink;

$link = SalesMeetingLink::create([
    'user_id' => $rep->id,
    'slug' => 'beth-discovery',
    'title' => 'Discovery call',
    'duration_minutes' => 30,
]);

$url = route('focal.meetings.show', ['slug' => $link->slug]); // https://your-app.test/meet/beth-discovery
```

## The booking page

`GET /meet/{slug}` (`focal.meetings.show`) renders `focal-sales::meetings.book`. The page offers fixed time slots (09:00, 10:00, 11:00, 13:00, 14:00, 15:00, and 16:00) on any date. It does not read `working_hours`, check the rep's calendar, or prevent two people booking the same slot. Override the view at `resources/views/vendor/focal-sales/meetings/book.blade.php` if you need different slots.

The form posts to `focal.meetings.book` (`POST /meet/{slug}/book`), which is rate limited by Core's `focal-public` limiter and validates:

| Field | Rules |
| --- | --- |
| `name` | required, string, 2–255 characters |
| `email` | required, email, max 255 |
| `phone` | nullable, string, max 30 |
| `date` | required, date, today or later |
| `time` | required, string |
| `notes` | nullable, string, max 1000 |

The date and time are combined with `Carbon::parse()` in the app's timezone. After booking, the visitor is redirected back to the page with a `status` flash message. That message says an invitation has been dispatched, but the package sends no email or calendar invitation; send one yourself if you need it.

## What booking does

`Focal\Sales\Actions\BookMeetingAction` does the work, and you can call it directly:

```php
public function execute(
    SalesMeetingLink $link,
    string $fullName,
    string $email,
    CarbonInterface $scheduledAt,
    ?string $phone = null,
    ?string $notes = null,
): array // ['contact' => Contact, 'activity' => Activity]
```

```php
use Focal\Sales\Actions\BookMeetingAction;
use Illuminate\Support\Carbon;

$result = app(BookMeetingAction::class)->execute(
    link: $link,
    fullName: 'Dana Scully',
    email: 'dana@example.com',
    scheduledAt: Carbon::tomorrow()->setTime(14, 0),
    phone: '+1 555 0100',
);

$result['contact'];  // Focal\Core\Models\Contact
$result['activity']; // Pending meeting activity
```

1. The email is trimmed and lower-cased, and an existing contact with that email is reused. Otherwise a contact is created with the name split on the first space into `first_name` and `last_name`, `lead_status` `Open`, and `owner_id` set to the rep. An existing contact's phone is filled in only if it was empty; its owner and other fields are not changed.
2. A `meeting` activity is logged on the contact with status `pending`, `due_at` set to the scheduled time, the rep as creator, and the title `{link title} with {contact name}`. The body is the visitor's notes, or `Booked online via /meet/{slug}`. Metadata holds `duration_minutes` and `booking_slug`.
3. All of the contact's `active` [sequence enrollments](sequences.md#enrollment-status) are set to `unenrolled`.
