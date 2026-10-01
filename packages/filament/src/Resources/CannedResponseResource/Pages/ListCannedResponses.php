<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\CannedResponseResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Focal\Filament\Resources\CannedResponseResource;

class ListCannedResponses extends ListRecords
{
    protected static string $resource = CannedResponseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
