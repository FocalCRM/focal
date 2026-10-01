<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\AdAudienceSyncResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Focal\Filament\Resources\AdAudienceSyncResource;

class ListAdAudienceSyncs extends ListRecords
{
    protected static string $resource = AdAudienceSyncResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New Audience Sync Bridge'),
        ];
    }
}
