<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\SlaPolicyResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Focal\Filament\Resources\SlaPolicyResource;

class ListSlaPolicies extends ListRecords
{
    protected static string $resource = SlaPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
