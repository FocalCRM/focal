<?php

declare(strict_types=1);

namespace Odden\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
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
use Odden\Filament\Resources\LeadScoringRuleResource\Pages\CreateLeadScoringRule;
use Odden\Filament\Resources\LeadScoringRuleResource\Pages\EditLeadScoringRule;
use Odden\Filament\Resources\LeadScoringRuleResource\Pages\ListLeadScoringRules;
use Odden\Marketing\Enums\LeadScoringEventType;
use Odden\Marketing\Models\LeadScoringRule;
use UnitEnum;

class LeadScoringRuleResource extends Resource
{
    protected static ?string $model = LeadScoringRule::class;

    protected static ?string $modelLabel = 'Lead Scoring Rule';

    protected static ?string $pluralModelLabel = 'Lead Scoring Rules';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Star;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Scoring Criteria & Weight')
                    ->schema([
                        TextInput::make('name')
                            ->label('Rule Name')
                            ->placeholder('e.g. Demo Form Submission (+25 pts)')
                            ->required()
                            ->maxLength(255),
                        Select::make('event_type')
                            ->label('Triggering Event Type')
                            ->options(collect(LeadScoringEventType::cases())->mapWithKeys(fn (LeadScoringEventType $e): array => [$e->value => $e->label()])->all())
                            ->required(),
                        TextInput::make('score_change')
                            ->label('Score Adjustment (Points)')
                            ->numeric()
                            ->default(15)
                            ->helperText('Points to add (+) or deduct (-) when this event occurs')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Active Scoring Rule')
                            ->default(true),
                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Describe the behavioral or profile attribute being scored...')
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
                    ->label('Rule')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('event_type')
                    ->label('Event')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof LeadScoringEventType ? $state->label() : (LeadScoringEventType::tryFrom((string) $state)?->label() ?? (string) $state)),
                TextColumn::make('score_change')
                    ->label('Score Delta')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state): string => ($state > 0 ? '+'.$state : (string) $state).' pts')
                    ->color(fn ($state): string => $state >= 0 ? 'success' : 'danger'),
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
            'index' => ListLeadScoringRules::route('/'),
            'create' => CreateLeadScoringRule::route('/create'),
            'edit' => EditLeadScoringRule::route('/{record}/edit'),
        ];
    }
}
