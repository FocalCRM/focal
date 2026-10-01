<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\MarketingEventResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\MarketingEventResource;

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
