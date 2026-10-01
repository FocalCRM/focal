<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\NpsSurveyResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Focal\Filament\Resources\NpsSurveyResource;

class ListNpsSurveys extends ListRecords
{
    protected static string $resource = NpsSurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New NPS Survey'),
        ];
    }
}
