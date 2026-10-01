<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\CampaignResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Focal\Filament\Resources\CampaignResource;

class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;
}
