<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\SalesPlaybookResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Focal\Filament\Resources\SalesPlaybookResource;

class ListSalesPlaybooks extends ListRecords
{
    protected static string $resource = SalesPlaybookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
