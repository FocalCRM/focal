<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\PipelineResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Focal\Filament\Resources\PipelineResource;

class CreatePipeline extends CreateRecord
{
    protected static string $resource = PipelineResource::class;
}
