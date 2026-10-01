<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\LandingPageResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Focal\Filament\Resources\LandingPageResource;

class ListLandingPages extends ListRecords
{
    protected static string $resource = LandingPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
