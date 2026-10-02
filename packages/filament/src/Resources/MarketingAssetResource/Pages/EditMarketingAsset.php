<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\MarketingAssetResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Odden\Filament\Resources\MarketingAssetResource;

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
