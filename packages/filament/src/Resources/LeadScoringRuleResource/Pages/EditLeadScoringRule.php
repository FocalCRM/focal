<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\LeadScoringRuleResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\LeadScoringRuleResource;

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
