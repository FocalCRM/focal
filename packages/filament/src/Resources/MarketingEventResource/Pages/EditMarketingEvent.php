<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\MarketingEventResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Odden\Filament\Resources\MarketingEventResource;

class EditMarketingEvent extends EditRecord
{
    protected static string $resource = MarketingEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
