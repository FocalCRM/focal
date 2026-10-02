<?php

declare(strict_types=1);

namespace Odden\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Odden\Core\Models\LifecycleStageTransition;

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
