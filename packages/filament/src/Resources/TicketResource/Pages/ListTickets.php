<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\TicketResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Focal\Filament\Resources\TicketResource;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('board')
                ->label('Tickets Board')
                ->icon(Heroicon::ViewColumns)
                ->color('gray')
                ->url(TicketResource::getUrl('board')),
            CreateAction::make()
                ->label('New Ticket'),
        ];
    }
}
