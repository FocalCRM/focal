<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\MarketingSubscriptionResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Focal\Filament\Resources\MarketingSubscriptionResource;

class ListMarketingSubscriptions extends ListRecords
{
    protected static string $resource = MarketingSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Suppressed Email'),
        ];
    }
}
