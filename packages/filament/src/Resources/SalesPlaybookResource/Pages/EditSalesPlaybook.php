<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\SalesPlaybookResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\SalesPlaybookResource;

class EditSalesPlaybook extends EditRecord
{
    protected static string $resource = SalesPlaybookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
