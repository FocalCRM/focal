<?php

declare(strict_types=1);

namespace Focal\Core\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * Stand-in for the Sales package's Deal: linked to contacts and companies through
 * focal_associations (deal is the parent), without Core depending on Sales.
 */
class Deal extends Model
{
    protected $table = 'fixture_deals';

    protected $guarded = [];
}
