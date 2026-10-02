<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\CrmListResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Odden\Filament\Resources\CrmListResource;

class ListCrmLists extends ListRecords
{
    protected static string $resource = CrmListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
