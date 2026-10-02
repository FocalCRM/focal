<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\DealResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Odden\Sales\Models\DealStageHistory;

class StageHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'stageHistory';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Stage Movement History';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('entered_at', 'desc')
            ->columns([
                TextColumn::make('fromStage.name')
                    ->label('From Stage')
                    ->placeholder('New Deal')
                    ->badge(),
                TextColumn::make('toStage.name')
                    ->label('To Stage')
                    ->badge()
                    ->color('primary')
                    ->weight('bold'),
                TextColumn::make('duration_in_stage_seconds')
                    ->label('Time in Stage')
                    ->state(function (DealStageHistory $record): string {
                        if ($record->duration_in_stage_seconds === null) {
                            return 'Current Stage';
                        }

                        $seconds = $record->duration_in_stage_seconds;
                        if ($seconds < 60) {
                            return "{$seconds}s";
                        }
                        if ($seconds < 3600) {
                            $mins = (int) round($seconds / 60);

                            return "{$mins}m";
                        }
                        $hours = (int) round($seconds / 3600);
                        if ($hours < 24) {
                            return "{$hours}h";
                        }
                        $days = (int) round($hours / 24);

                        return "{$days}d";
                    })
                    ->badge(),
                TextColumn::make('user.name')
                    ->label('Moved By')
                    ->placeholder('System'),
                TextColumn::make('entered_at')
                    ->label('Entered At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('exited_at')
                    ->label('Exited At')
                    ->dateTime()
                    ->placeholder('Current'),
            ]);
    }
}
