<?php

declare(strict_types=1);

namespace Odden\Core\Events;

use Odden\Core\Models\Contact;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched by MergeContactsAction inside its transaction, after Core has moved its own records
 * to the primary and before the secondary is soft-deleted. Packages listen for it to move the
 * records they own (rows keyed by contact_id, for example). A listener that throws rolls the
 * whole merge back.
 */
class ContactsMerged
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Contact $primary,
        public Contact $secondary
    ) {}
}
