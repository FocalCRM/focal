<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\MarketingWorkflowResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Odden\Filament\Resources\MarketingWorkflowResource;

class ListMarketingWorkflows extends ListRecords
{
    protected static string $resource = MarketingWorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
