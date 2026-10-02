<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\SalesEmailTemplateResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Odden\Filament\Resources\SalesEmailTemplateResource;

class EditSalesEmailTemplate extends EditRecord
{
    protected static string $resource = SalesEmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
