<?php

declare(strict_types=1);

namespace Odden\Filament\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Odden\Core\Enums\ListType;
use Odden\Core\Models\Company;
use Odden\Core\Models\Contact;
use Odden\Core\Models\CrmList;
use Odden\Filament\Resources\CrmListResource\Pages\CreateCrmList;
use Odden\Filament\Resources\CrmListResource\Pages\EditCrmList;
use Odden\Filament\Resources\CrmListResource\Pages\ListCrmLists;
use Odden\Filament\Resources\CrmListResource\Pages\ViewCrmList;
use Odden\Filament\Resources\CrmListResource\RelationManagers\MembersRelationManager;
use Odden\Filament\Support\OddenAuthorization;
use UnitEnum;

class CrmListResource extends Resource
{
    protected static ?string $model = CrmList::class;

    protected static ?string $modelLabel = 'List';

    protected static ?string $pluralModelLabel = 'Lists';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::QueueList;

    protected static UnitEnum|string|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('List Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('List Name')
                            ->placeholder('e.g. High Value Leads')
                            ->required()
                            ->maxLength(255),
                        Select::make('entity_type')
                            ->label('Target Entity')
                            ->options([
                                (new Contact)->getMorphClass() => 'Contacts',
                                (new Company)->getMorphClass() => 'Companies',
                            ])
                            ->required(),
                        Select::make('type')
                            ->label('List Type')
                            ->options(collect(ListType::cases())->mapWithKeys(
                                fn (ListType $type) => [$type->value => $type->label()]
                            ))
                            ->default(ListType::Active->value)
                            ->reactive()
                            ->required(),
                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Brief description of purpose or targeting...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Segmentation Rules / Criteria')
                    ->description('For Active smart lists, records matching these rules are dynamically synced.')
                    ->visible(fn (callable $get): bool => $get('type') === ListType::Active->value)
                    ->schema([
                        Repeater::make('criteria')
                            ->label('Rules')
                            ->schema([
                                TextInput::make('field')
                                    ->label('Property / Field')
                                    ->placeholder('e.g. lifecycle_stage, email, lead_score')
                                    ->required(),
                                Select::make('operator')
                                    ->label('Condition')
                                    ->options([
                                        'equals' => 'Equals',
                                        'not_equals' => 'Does not equal',
                                        'contains' => 'Contains',
                                        'greater_than' => 'Greater than',
                                        'less_than' => 'Less than',
                                        'is_set' => 'Is set / Not empty',
                                        'is_not_set' => 'Is not set / Empty',
                                    ])
                                    ->default('equals')
                                    ->required(),
                                TextInput::make('value')
                                    ->label('Value')
                                    ->placeholder('Comparison value'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add Rule')
                            ->defaultItems(1)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('List Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('entity_type')
                    ->label('Target')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => class_basename((string) $state)),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof ListType ? $state->label() : ucfirst((string) $state))
                    ->color(fn ($state): string => match ($state instanceof ListType ? $state : ListType::tryFrom((string) $state)) {
                        ListType::Active => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('memberships_count')
                    ->counts('memberships')
                    ->label('Members')
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(collect(ListType::cases())->mapWithKeys(
                        fn (ListType $type) => [$type->value => $type->label()]
                    )),
            ])
            ->recordActions([
                Action::make('sync')
                    ->label('Sync')
                    ->icon(Heroicon::ArrowPath)
                    ->color('primary')
                    ->visible(fn (CrmList $record): bool => $record->type === ListType::Active)
                    ->authorize(OddenAuthorization::forRecord('update', self::class))
                    ->action(function (CrmList $record): void {
                        $count = $record->syncActiveMembers();

                        Notification::make()
                            ->title('List Synchronized')
                            ->body("{$count} members synced.")
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCrmLists::route('/'),
            'create' => CreateCrmList::route('/create'),
            'view' => ViewCrmList::route('/{record}'),
            'edit' => EditCrmList::route('/{record}/edit'),
        ];
    }
}
