<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\DealResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Focal\Filament\Resources\DealResource;
use Focal\Sales\Models\Deal;
use Focal\Sales\Models\DealStageHistory;

class CreateDeal extends CreateRecord
{
    protected static string $resource = DealResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_id'] ??= auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Deal $record */
        $record = $this->record;

        DealStageHistory::create([
            'deal_id' => $record->id,
            'from_stage_id' => null,
            'to_stage_id' => $record->stage_id,
            'user_id' => auth()->id(),
            'entered_at' => now(),
        ]);
    }
}
