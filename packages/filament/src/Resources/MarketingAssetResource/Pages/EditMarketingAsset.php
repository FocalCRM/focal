<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\MarketingAssetResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\MarketingAssetResource;

class EditMarketingAsset extends EditRecord
{
    protected static string $resource = MarketingAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
