<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\ContactResource\RelationManagers;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Odden\Core\Models\Contact;
use Odden\Filament\Resources\ContactResource;
use Odden\Filament\Support\OddenAuthorization;
use Odden\Marketing\Enums\LeadScoringEventType;
use Odden\Marketing\Models\LeadScoreLog;

class LeadScoreLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'leadScoreLogs';

    protected static ?string $title = 'Lead Score & Decay History';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('event_description')
                ->label('Event Description')
                ->required(),
            TextInput::make('score_change')
                ->label('Score Change')
                ->numeric()
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('event_description')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('event_type')
                    ->label('Event')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'form_submission' => 'Form Submit',
                        'email_opened' => 'Email Open',
                        'email_clicked' => 'Email Click',
                        'inactivity_decay' => 'Inactivity Decay',
                        'property_match' => 'Property Fit',
                        'unsubscribed' => 'Unsubscribed',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'form_submission', 'email_clicked', 'property_match' => 'success',
                        'email_opened' => 'info',
                        'inactivity_decay', 'unsubscribed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('event_description')
                    ->label('Activity & Reason')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('score_change')
                    ->label('Delta')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => ($state > 0 ? "+{$state}" : (string) $state).' pts')
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('score_after')
                    ->label('Total Score')
                    ->weight('bold')
                    ->suffix(' pts')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Occurred')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('manualScoreAdjustment')
                    ->label('Adjust Score')
                    ->authorize(fn (): bool => OddenAuthorization::allows('update', $this->getOwnerRecord(), ContactResource::class))
                    ->icon(Heroicon::Sparkles)
                    ->color('primary')
                    ->modalHeading('Manual Lead Score Adjustment')
                    ->modalDescription('Manually increment or deduct lead scoring points for this contact.')
                    ->schema([
                        TextInput::make('score_delta')
                            ->label('Points to Add / Deduct')
                            ->helperText('Use positive integer to add (e.g. 15), negative to deduct (e.g. -10)')
                            ->numeric()
                            ->required(),
                        TextInput::make('reason')
                            ->label('Reason / Context')
                            ->placeholder('e.g. Spoke at executive dinner')
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        /** @var Contact $contact */
                        $contact = $this->getOwnerRecord();
                        $delta = (int) $data['score_delta'];
                        $reason = (string) $data['reason'];

                        $oldScore = $contact->lead_score;
                        $newScore = max(0, $oldScore + $delta);

                        LeadScoreLog::create([
                            'contact_id' => $contact->id,
                            'event_type' => LeadScoringEventType::PropertyMatch->value,
                            'event_description' => "Manual Adjustment: {$reason}",
                            'score_change' => $delta,
                            'score_after' => $newScore,
                            'created_at' => now(),
                        ]);

                        $contact->update([
                            'lead_score' => $newScore,
                            'lead_score_updated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Lead Score Updated')
                            ->body("Contact score is now {$newScore} points.")
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
