<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\SalesMeetingLinkResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Focal\Filament\Resources\SalesMeetingLinkResource;

class ListSalesMeetingLinks extends ListRecords
{
    protected static string $resource = SalesMeetingLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
