<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\SalesQuotaResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\SalesQuotaResource;

class EditSalesQuota extends EditRecord
{
    protected static string $resource = SalesQuotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
