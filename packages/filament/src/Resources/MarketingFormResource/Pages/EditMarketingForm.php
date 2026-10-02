<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\MarketingFormResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Odden\Filament\Resources\MarketingFormResource;

class EditMarketingForm extends EditRecord
{
    protected static string $resource = MarketingFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
