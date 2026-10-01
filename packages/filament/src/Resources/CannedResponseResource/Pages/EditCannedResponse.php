<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\CannedResponseResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\CannedResponseResource;

class EditCannedResponse extends EditRecord
{
    protected static string $resource = CannedResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
