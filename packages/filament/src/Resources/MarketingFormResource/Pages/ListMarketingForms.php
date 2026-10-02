<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\MarketingFormResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Odden\Filament\Resources\MarketingFormResource;

class ListMarketingForms extends ListRecords
{
    protected static string $resource = MarketingFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
