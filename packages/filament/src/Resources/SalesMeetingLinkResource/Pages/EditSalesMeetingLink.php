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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['working_hours'] = SalesMeetingLinkResource::normalizeWorkingHours($data['working_hours'] ?? null);

        return $data;
    }
}
