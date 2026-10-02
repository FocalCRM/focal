<?php

declare(strict_types=1);

namespace Odden\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Odden\Core\Models\Company;

class CompanyEnriched
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $enrichmentData
     */
    public function __construct(
        public Company $company,
        public array $enrichmentData
    ) {}
}
