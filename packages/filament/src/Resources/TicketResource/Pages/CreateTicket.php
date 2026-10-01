<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\TicketResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Focal\Filament\Resources\TicketResource;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;
}
