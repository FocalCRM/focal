<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\PropertyDefinitionResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\PropertyDefinitionResource;

class EditPropertyDefinition extends EditRecord
{
    protected static string $resource = PropertyDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
