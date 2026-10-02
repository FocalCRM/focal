<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\MarketingAssetResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Odden\Filament\Resources\MarketingAssetResource;

class ListMarketingAssets extends ListRecords
{
    protected static string $resource = MarketingAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New Digital Asset'),
        ];
    }
}
