<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\SalesQuotaResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Odden\Filament\Resources\SalesQuotaResource;

class ListSalesQuotas extends ListRecords
{
    protected static string $resource = SalesQuotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
