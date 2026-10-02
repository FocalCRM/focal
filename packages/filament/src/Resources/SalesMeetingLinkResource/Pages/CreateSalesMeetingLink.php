<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\SalesMeetingLinkResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\SalesMeetingLinkResource;

class CreateSalesMeetingLink extends CreateRecord
{
    protected static string $resource = SalesMeetingLinkResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['working_hours'] = SalesMeetingLinkResource::normalizeWorkingHours($data['working_hours'] ?? null);

        return $data;
    }
}
