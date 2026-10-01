<?php

declare(strict_types=1);

namespace Focal\Core\Events;

use Focal\Core\Models\Activity;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityLogged
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Activity $activity
    ) {}
}
