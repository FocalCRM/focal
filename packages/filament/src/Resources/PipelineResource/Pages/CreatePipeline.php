<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\PipelineResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\PipelineResource;

class CreatePipeline extends CreateRecord
{
    protected static string $resource = PipelineResource::class;
}
