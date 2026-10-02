<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\PropertyDefinitionResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\PropertyDefinitionResource;

class CreatePropertyDefinition extends CreateRecord
{
    protected static string $resource = PropertyDefinitionResource::class;
}
