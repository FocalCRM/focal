<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\TicketResource\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Focal\Filament\Resources\TicketResource;
use Focal\Filament\Support\FocalAuthorization;
use Focal\Service\Enums\TicketStatus;
use Focal\Service\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

class KanbanTickets extends Page
{
    protected static string $resource = TicketResource::class;

    protected static ?string $title = 'Support Tickets Board';

    protected static ?string $navigationLabel = 'Tickets Board';

    protected string $view = 'focal-filament::pages.ticket-kanban';

    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function canAccess(array $parameters = []): bool
    {
        return FocalAuthorization::canViewAny([TicketResource::class]);
    }

    /**
     * @return array<int, array{status: TicketStatus, tickets: Collection<int, Ticket>}>
     */
    public function getColumnsProperty(): array
    {
        $statuses = TicketStatus::cases();
        $result = [];

        foreach ($statuses as $status) {
            $tickets = FocalAuthorization::query(TicketResource::class, Ticket::class)
                ->where('status', $status->value)
                ->with(['contact', 'company', 'owner', 'slaPolicy'])
                ->orderBy('created_at', 'desc')
                ->get();

            $result[] = [
                'status' => $status,
                'tickets' => $tickets,
            ];
        }

        return $result;
    }

    public function moveTicket(int $ticketId, string $statusValue): void
    {
        $ticket = FocalAuthorization::findAndAuthorize(TicketResource::class, Ticket::class, $ticketId, 'update');

        $status = TicketStatus::tryFrom($statusValue);
        if ($status === null) {
            return;
        }

        if ($status === TicketStatus::Resolved) {
            $ticket->resolve();
        } elseif ($status === TicketStatus::Closed) {
            $ticket->close();
        } else {
            $ticket->update(['status' => $status]);
        }

        Notification::make()
            ->title('Ticket Updated')
            ->body("Ticket #{$ticket->ticket_number} moved to [{$status->label()}]")
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('table')
                ->label('Table View')
                ->icon(Heroicon::TableCells)
                ->color('gray')
                ->url(TicketResource::getUrl('index')),
            Action::make('create')
                ->label('New Ticket')
                ->icon(Heroicon::Plus)
                ->color('primary')
                ->url(TicketResource::getUrl('create')),
        ];
    }
}
