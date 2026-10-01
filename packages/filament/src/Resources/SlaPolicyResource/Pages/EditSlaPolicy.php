<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\SlaPolicyResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\SlaPolicyResource;

class EditSlaPolicy extends EditRecord
{
    protected static string $resource = SlaPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
