<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\TicketResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\TicketResource;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;
}
