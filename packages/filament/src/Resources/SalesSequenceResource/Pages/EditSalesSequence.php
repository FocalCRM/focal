<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\SalesSequenceResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\SalesSequenceResource;

class EditSalesSequence extends EditRecord
{
    protected static string $resource = SalesSequenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
