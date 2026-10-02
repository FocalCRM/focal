<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\LandingPageResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\LandingPageResource;

class CreateLandingPage extends CreateRecord
{
    protected static string $resource = LandingPageResource::class;
}
