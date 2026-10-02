<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\LeadScoringRuleResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Odden\Filament\Resources\LeadScoringRuleResource;

class EditLeadScoringRule extends EditRecord
{
    protected static string $resource = LeadScoringRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
