<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\SalesEmailTemplateResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Odden\Filament\Resources\SalesEmailTemplateResource;

class ListSalesEmailTemplates extends ListRecords
{
    protected static string $resource = SalesEmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
