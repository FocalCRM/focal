<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\LandingPageResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\LandingPageResource;

class EditLandingPage extends EditRecord
{
    protected static string $resource = LandingPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
