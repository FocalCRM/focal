<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\MarketingEventResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\MarketingEventResource;

class CreateMarketingEvent extends CreateRecord
{
    protected static string $resource = MarketingEventResource::class;
}
