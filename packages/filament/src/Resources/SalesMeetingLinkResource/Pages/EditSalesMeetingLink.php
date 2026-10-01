<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\SalesMeetingLinkResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\SalesMeetingLinkResource;

class EditSalesMeetingLink extends EditRecord
{
    protected static string $resource = SalesMeetingLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
