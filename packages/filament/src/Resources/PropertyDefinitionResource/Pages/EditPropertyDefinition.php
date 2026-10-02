<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\PropertyDefinitionResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Odden\Filament\Resources\PropertyDefinitionResource;

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
