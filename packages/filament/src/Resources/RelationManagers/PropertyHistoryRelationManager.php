<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PropertyHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'propertyHistory';

    protected static ?string $recordTitleAttribute = 'property_name';

    protected static ?string $title = 'Property History / Audit Trail';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('property_name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('property_name')
                    ->label('Property / Field')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('old_value')
                    ->label('Old Value')
                    ->limit(40)
                    ->placeholder('—'),
                TextColumn::make('new_value')
                    ->label('New Value')
                    ->limit(40)
                    ->placeholder('—'),
                TextColumn::make('user.name')
                    ->label('Changed By')
                    ->placeholder('System')
                    ->default('System'),
                TextColumn::make('source')
                    ->label('Source')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Changed At')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
