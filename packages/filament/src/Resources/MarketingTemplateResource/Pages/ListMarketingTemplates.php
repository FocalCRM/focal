<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\MarketingTemplateResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Focal\Filament\Resources\MarketingTemplateResource;

class ListMarketingTemplates extends ListRecords
{
    protected static string $resource = MarketingTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
