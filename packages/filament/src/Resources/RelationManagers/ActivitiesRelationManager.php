<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Focal\Core\Enums\ActivityStatus;
use Focal\Core\Enums\ActivityType;
use Focal\Core\Models\Activity;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $recordTitleAttribute = 'title';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')
                ->label('Activity Type')
                ->options(collect(ActivityType::cases())->mapWithKeys(
                    fn (ActivityType $type) => [$type->value => $type->label()]
                ))
                ->default(ActivityType::Note->value)
                ->required(),
            TextInput::make('title')
                ->label('Subject / Title')
                ->required()
                ->maxLength(255),
            Textarea::make('body')
                ->label('Details / Body')
                ->rows(3),
            Select::make('status')
                ->label('Status')
                ->options(collect(ActivityStatus::cases())->mapWithKeys(
                    fn (ActivityStatus $status) => [$status->value => $status->label()]
                ))
                ->default(ActivityStatus::Completed->value)
                ->required(),
            DateTimePicker::make('due_at')
                ->label('Due Date')
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof ActivityType ? $state->label() : ucfirst((string) $state))
                    ->color(fn ($state): string => match ($state instanceof ActivityType ? $state : ActivityType::tryFrom((string) $state)) {
                        ActivityType::Task => 'warning',
                        ActivityType::Call => 'info',
                        ActivityType::Meeting => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('title')
                    ->label('Subject')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Activity $record): ?string => $record->body),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof ActivityStatus ? $state->label() : ucfirst((string) $state))
                    ->color(fn ($state): string => match ($state instanceof ActivityStatus ? $state : ActivityStatus::tryFrom((string) $state)) {
                        ActivityStatus::Completed => 'success',
                        ActivityStatus::Cancelled => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('due_at')
                    ->label('Due Date')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('creator.name')
                    ->label('Logged By')
                    ->placeholder('System')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Logged At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Log Activity')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['creator_id'] = auth()->id();
                        if (($data['status'] ?? null) === ActivityStatus::Completed->value && empty($data['completed_at'])) {
                            $data['completed_at'] = now();
                        }

                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('complete')
                    ->label('Complete')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->visible(fn (Activity $record): bool => $record->status === ActivityStatus::Pending)
                    ->action(fn (Activity $record) => $record->update([
                        'status' => ActivityStatus::Completed,
                        'completed_at' => now(),
                    ])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
