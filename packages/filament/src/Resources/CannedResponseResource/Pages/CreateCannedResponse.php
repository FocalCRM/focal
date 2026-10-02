<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\CannedResponseResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\CannedResponseResource;

class CreateCannedResponse extends CreateRecord
{
    protected static string $resource = CannedResponseResource::class;
}
