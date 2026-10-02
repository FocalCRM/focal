<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\CampaignResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\CampaignResource;

class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;
}
