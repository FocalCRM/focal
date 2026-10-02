<?php

declare(strict_types=1);

namespace Odden\Core\Events;

use Odden\Core\Models\Association;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RecordsAssociated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Association $association
    ) {}
}
