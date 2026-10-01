<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\LeadRoutingRuleResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Focal\Filament\Resources\LeadRoutingRuleResource;

class ListLeadRoutingRules extends ListRecords
{
    protected static string $resource = LeadRoutingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
