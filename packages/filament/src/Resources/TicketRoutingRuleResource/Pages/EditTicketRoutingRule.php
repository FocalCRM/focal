<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\TicketRoutingRuleResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\TicketRoutingRuleResource;

class EditTicketRoutingRule extends EditRecord
{
    protected static string $resource = TicketRoutingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
