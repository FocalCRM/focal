<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\LeadScoringRuleResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Odden\Filament\Resources\LeadScoringRuleResource;

class ListLeadScoringRules extends ListRecords
{
    protected static string $resource = LeadScoringRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
