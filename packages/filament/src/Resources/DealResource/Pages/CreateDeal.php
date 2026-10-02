<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\DealResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\DealResource;
use Odden\Sales\Models\Deal;
use Odden\Sales\Models\DealStageHistory;

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
