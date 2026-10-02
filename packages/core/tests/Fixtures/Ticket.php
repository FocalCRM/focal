<?php

declare(strict_types=1);

namespace Focal\Core\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * Stand-in for the Service package's Ticket: belongs to a contact and a company by foreign key.
 */
class Ticket extends Model
{
    protected $table = 'fixture_tickets';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_sla_response_breached' => 'boolean',
            'is_sla_resolution_breached' => 'boolean',
        ];
    }
}
