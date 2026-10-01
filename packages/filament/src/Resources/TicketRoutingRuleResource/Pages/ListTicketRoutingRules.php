<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\TicketRoutingRuleResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Focal\Filament\Resources\TicketRoutingRuleResource;

class ListTicketRoutingRules extends ListRecords
{
    protected static string $resource = TicketRoutingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
