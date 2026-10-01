<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\PipelineResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Focal\Filament\Resources\PipelineResource;

class ListPipelines extends ListRecords
{
    protected static string $resource = PipelineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
