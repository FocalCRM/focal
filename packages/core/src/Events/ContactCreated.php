<?php

declare(strict_types=1);

namespace Odden\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Odden\Core\Models\Contact;

class ContactCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Contact $contact
    ) {}
}
