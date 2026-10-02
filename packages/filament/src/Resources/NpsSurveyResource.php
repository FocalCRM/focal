<?php

declare(strict_types=1);

namespace Odden\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Odden\Filament\Resources\NpsSurveyResource\Pages\CreateNpsSurvey;
use Odden\Filament\Resources\NpsSurveyResource\Pages\EditNpsSurvey;
use Odden\Filament\Resources\NpsSurveyResource\Pages\ListNpsSurveys;
use Odden\Marketing\Models\NpsSurvey;
use UnitEnum;

class NpsSurveyResource extends Resource
{
    protected static ?string $model = NpsSurvey::class;

    protected static ?string $modelLabel = 'Nps Survey';

    protected static ?string $pluralModelLabel = 'Nps Surveys';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Heart;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Survey Configuration')
                    ->schema([
                        TextInput::make('name')
                            ->label('Survey Campaign Name')
                            ->placeholder('e.g. Q4 Post-Onboarding Customer NPS')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Active & Collecting Feedback')
                            ->default(true),
                        Textarea::make('description')
                            ->label('Survey Objective / Notes')
                            ->placeholder('Triggered when deals close or onboarding tickets complete')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Survey Name')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('responses_count')
                    ->counts('responses')
                    ->label('Responses')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nps_score')
                    ->label('Net Promoter Score')
                    ->getStateUsing(fn (NpsSurvey $record): int => $record->calculateNpsScore())
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => "NPS {$state}")
                    ->color(fn (int $state): string => match (true) {
                        $state >= 50 => 'success',
                        $state >= 0 => 'info',
                        default => 'danger',
                    }),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNpsSurveys::route('/'),
            'create' => CreateNpsSurvey::route('/create'),
            'edit' => EditNpsSurvey::route('/{record}/edit'),
        ];
    }
}
