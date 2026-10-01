<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\CrmListResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Focal\Core\Enums\ListType;
use Focal\Core\Models\CrmList;
use Focal\Filament\Resources\CrmListResource;

class CreateCrmList extends CreateRecord
{
    protected static string $resource = CrmListResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var CrmList $record */
        $record = $this->record;

        if ($record->type === ListType::Active) {
            $record->syncActiveMembers();
        }
    }
}
