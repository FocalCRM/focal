<?php

declare(strict_types=1);

namespace Focal\Core\Events;

use Focal\Core\Models\Company;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched by MergeCompaniesAction inside its transaction, after Core has moved its own records
 * to the primary, and before the primary's health score is recalculated and the secondary is
 * soft-deleted. Packages listen for it to move the records they own (rows keyed by company_id,
 * for example), so the recalculated score sees them. A listener that throws rolls the whole
 * merge back.
 */
class CompaniesMerged
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Company $primary,
        public Company $secondary
    ) {}
}
