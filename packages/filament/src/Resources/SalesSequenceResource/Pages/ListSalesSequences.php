<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\SalesSequenceResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Focal\Filament\Resources\SalesSequenceResource;
use Focal\Filament\Support\FocalAuthorization;
use Focal\Sales\Actions\ProcessCadencesAction;
use Focal\Sales\Models\SalesSequence;

class ListSalesSequences extends ListRecords
{
    protected static string $resource = SalesSequenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('processDueCadences')
                ->label('Process Due Cadences')
                ->icon(Heroicon::Play)
                ->color('info')
                // Processes due steps across every active sequence, so it needs `update` on all of them.
                ->authorize(fn (): bool => FocalAuthorization::query(SalesSequenceResource::class, SalesSequence::class)
                    ->where('is_active', true)
                    ->get()
                    ->every(fn (SalesSequence $sequence): bool => FocalAuthorization::allows('update', $sequence, SalesSequenceResource::class)))
                ->action(function (): void {
                    $stats = app(ProcessCadencesAction::class)->execute();

                    Notification::make()
                        ->title('Cadences Processed')
                        ->body("Processed {$stats['processed']} enrollments ({$stats['emails_sent']} emails sent, {$stats['tasks_created']} tasks created).")
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
