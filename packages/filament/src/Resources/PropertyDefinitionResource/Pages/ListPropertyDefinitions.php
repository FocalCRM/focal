<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\PropertyDefinitionResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Odden\Filament\Resources\PropertyDefinitionResource;

class ListPropertyDefinitions extends ListRecords
{
    protected static string $resource = PropertyDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
