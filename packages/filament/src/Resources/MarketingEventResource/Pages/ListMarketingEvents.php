<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\MarketingEventResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Focal\Filament\Resources\MarketingEventResource;

class ListMarketingEvents extends ListRecords
{
    protected static string $resource = MarketingEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New Marketing Event'),
        ];
    }
}
