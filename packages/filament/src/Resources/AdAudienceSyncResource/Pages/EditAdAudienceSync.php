<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\AdAudienceSyncResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Odden\Filament\Resources\AdAudienceSyncResource;

class EditAdAudienceSync extends EditRecord
{
    protected static string $resource = AdAudienceSyncResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
