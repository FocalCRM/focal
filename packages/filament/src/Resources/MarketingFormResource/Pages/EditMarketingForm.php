<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\MarketingFormResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\MarketingFormResource;

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
