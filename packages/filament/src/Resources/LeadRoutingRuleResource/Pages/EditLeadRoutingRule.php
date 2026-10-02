<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\LeadRoutingRuleResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Odden\Filament\Resources\LeadRoutingRuleResource;

class EditLeadRoutingRule extends EditRecord
{
    protected static string $resource = LeadRoutingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
