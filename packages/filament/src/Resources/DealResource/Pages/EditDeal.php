<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\DealResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\DealResource;

class EditDeal extends EditRecord
{
    protected static string $resource = DealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
