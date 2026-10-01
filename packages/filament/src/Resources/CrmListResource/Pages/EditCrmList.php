<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\CrmListResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Core\Enums\ListType;
use Focal\Core\Models\CrmList;
use Focal\Filament\Resources\CrmListResource;

class EditCrmList extends EditRecord
{
    protected static string $resource = CrmListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var CrmList $record */
        $record = $this->record;

        if ($record->type === ListType::Active) {
            $record->syncActiveMembers();
        }
    }
}
