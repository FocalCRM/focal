<?php

declare(strict_types=1);

namespace Odden\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Odden\Core\Support\UserModel;
use Odden\Filament\Resources\TicketRoutingRuleResource\Pages\CreateTicketRoutingRule;
use Odden\Filament\Resources\TicketRoutingRuleResource\Pages\EditTicketRoutingRule;
use Odden\Filament\Resources\TicketRoutingRuleResource\Pages\ListTicketRoutingRules;
use Odden\Service\Models\TicketRoutingRule;
use UnitEnum;

class TicketRoutingRuleResource extends Resource
{
    protected static ?string $model = TicketRoutingRule::class;

    protected static ?string $modelLabel = 'Ticket Routing Rule';

    protected static ?string $pluralModelLabel = 'Ticket Routing Rules';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowsRightLeft;

    protected static UnitEnum|string|null $navigationGroup = 'Service';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ticket Routing Configuration')
                    ->schema([
                        TextInput::make('name')
                            ->label('Rule Name')
                            ->placeholder('e.g. Critical Issue Auto-Escalation')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->label('Evaluation Priority')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers are evaluated first.')
                            ->required(),
                        Select::make('assigned_user_ids')
                            ->label('Eligible Support Agents (Round-Robin Pool)')
                            ->options(fn (): array => UserModel::query()->pluck('name', 'id')->all())
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->columnSpanFull(),
                        KeyValue::make('criteria')
                            ->label('Matching Criteria (e.g. priority: urgent, source: email, keyword: billing)')
                            ->keyLabel('Filter Key')
                            ->valueLabel('Expected Value')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Active Rule')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('Priority')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Rule Name')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assigned_user_ids')
                    ->label('Agents Pool')
                    ->formatStateUsing(fn (TicketRoutingRule $record): string => count($record->assigned_user_ids).' agents'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ListTicketRoutingRules::route('/'),
            'create' => CreateTicketRoutingRule::route('/create'),
            'edit' => EditTicketRoutingRule::route('/{record}/edit'),
        ];
    }
}
