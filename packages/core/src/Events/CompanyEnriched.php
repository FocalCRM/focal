<?php

declare(strict_types=1);

namespace Odden\Core\Events;

use Odden\Core\Models\Company;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
