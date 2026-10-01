<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\DealResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Focal\Filament\Resources\DealResource;
use Focal\Filament\Widgets\DealPipelineForecastWidget;

class ListDeals extends ListRecords
{
    protected static string $resource = DealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('board')
                ->label('Pipeline Board')
                ->icon(Heroicon::ViewColumns)
                ->color('gray')
                ->url(DealResource::getUrl('board')),
            CreateAction::make()
                ->label('New Deal'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DealPipelineForecastWidget::class,
        ];
    }
}
