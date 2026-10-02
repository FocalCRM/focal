<?php

declare(strict_types=1);

namespace Odden\Core\Events;

use Odden\Core\Models\CustomObjectRecord;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomObjectRecordCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public CustomObjectRecord $record
    ) {}
}
