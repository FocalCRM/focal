<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\MarketingAssetResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\MarketingAssetResource;

class CreateMarketingAsset extends CreateRecord
{
    protected static string $resource = MarketingAssetResource::class;
}
