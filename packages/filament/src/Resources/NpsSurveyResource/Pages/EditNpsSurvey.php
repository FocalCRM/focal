<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\NpsSurveyResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Odden\Filament\Resources\NpsSurveyResource;

class EditNpsSurvey extends EditRecord
{
    protected static string $resource = NpsSurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
