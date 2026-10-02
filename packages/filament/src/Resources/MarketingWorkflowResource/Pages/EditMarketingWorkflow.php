<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\MarketingWorkflowResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Odden\Filament\Resources\MarketingWorkflowResource;

class EditMarketingWorkflow extends EditRecord
{
    protected static string $resource = MarketingWorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
