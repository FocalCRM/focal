<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\ContactResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Focal\Filament\Resources\ContactResource;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;
}
