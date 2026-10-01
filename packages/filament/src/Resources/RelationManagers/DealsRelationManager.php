<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Focal\Sales\Enums\DealStatus;
use Focal\Sales\Models\Deal;

class DealsRelationManager extends RelationManager
{
    protected static string $relationship = 'deals';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Associated Deals';

    protected static bool $isLazy = false;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Deal Name')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Deal Name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('pipeline.name')
                    ->label('Pipeline')
                    ->badge(),
                TextColumn::make('stage.name')
                    ->label('Stage')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn (Deal $record): string => $record->currency)
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof DealStatus ? $state->label() : ucfirst((string) $state))
                    ->color(fn ($state): string => match ($state instanceof DealStatus ? $state : DealStatus::tryFrom((string) $state)) {
                        DealStatus::Won => 'success',
                        DealStatus::Lost => 'danger',
                        default => 'info',
                    }),
                TextColumn::make('expected_close_date')
                    ->label('Target Close')
                    ->date()
                    ->sortable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('type')
                            ->label('Role / Type')
                            ->options([
                                'primary' => 'Primary',
                                'secondary' => 'Secondary',
                                'influencer' => 'Influencer',
                            ])
                            ->default('primary')
                            ->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['parent_type'] = Deal::class;
                        $data['child_type'] = $this->getOwnerRecord()->getMorphClass();

                        return $data;
                    }),
            ])
            ->recordActions([
                DetachAction::make(),
            ]);
    }
}
