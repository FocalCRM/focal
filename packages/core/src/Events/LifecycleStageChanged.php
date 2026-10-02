<?php

declare(strict_types=1);

namespace Odden\Core\Events;

use Odden\Core\Models\LifecycleStageTransition;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LifecycleStageChanged
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Model $record,
        public LifecycleStageTransition $transition
    ) {}
}
