<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\LandingPageResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Focal\Filament\Resources\LandingPageResource;

class CreateLandingPage extends CreateRecord
{
    protected static string $resource = LandingPageResource::class;
}
