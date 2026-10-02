<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\ContactResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\ContactResource;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;
}
