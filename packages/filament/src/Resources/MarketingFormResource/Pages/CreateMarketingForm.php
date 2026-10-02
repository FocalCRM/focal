<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\MarketingFormResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\MarketingFormResource;

class CreateMarketingForm extends CreateRecord
{
    protected static string $resource = MarketingFormResource::class;
}
