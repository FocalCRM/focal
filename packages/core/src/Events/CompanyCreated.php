<?php

declare(strict_types=1);

namespace Focal\Core\Events;

use Focal\Core\Models\Company;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompanyCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Company $company
    ) {}
}
