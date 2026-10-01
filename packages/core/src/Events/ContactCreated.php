<?php

declare(strict_types=1);

namespace Focal\Core\Events;

use Focal\Core\Models\Contact;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Contact $contact
    ) {}
}
