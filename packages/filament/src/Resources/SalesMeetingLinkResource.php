<?php

declare(strict_types=1);

namespace Focal\Filament\Resources;

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
use Focal\Core\Support\UserModel;
use Focal\Filament\Resources\SalesMeetingLinkResource\Pages\CreateSalesMeetingLink;
use Focal\Filament\Resources\SalesMeetingLinkResource\Pages\EditSalesMeetingLink;
use Focal\Filament\Resources\SalesMeetingLinkResource\Pages\ListSalesMeetingLinks;
use Focal\Sales\Models\SalesMeetingLink;
use UnitEnum;

class SalesMeetingLinkResource extends Resource
{
    protected static ?string $model = SalesMeetingLink::class;

    protected static ?string $modelLabel = 'Meeting Link';

    protected static ?string $pluralModelLabel = 'Meeting Links';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static UnitEnum|string|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Meeting Details')
                    ->schema([
                        Select::make('user_id')
                            ->label('Assigned Sales Rep')
                            ->options(fn (): array => UserModel::query()->pluck('name', 'id')->all())
                            ->default(fn (): ?int => auth()->id() !== null ? (int) auth()->id() : null)
                            ->searchable()
                            ->required(),
                        TextInput::make('title')
                            ->label('Meeting Title')
                            ->placeholder('e.g. 30 Min Discovery Call')
                            ->default('30 Min Meeting')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Booking Slug (URL handle)')
                            ->prefix(url('/meet').'/')
                            ->placeholder('e.g. beth-caldwell')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('duration_minutes')
                            ->label('Duration')
                            ->options([
                                15 => '15 Minutes',
                                30 => '30 Minutes',
                                45 => '45 Minutes',
                                60 => '60 Minutes',
                            ])
                            ->default(30)
                            ->required(),
                        Textarea::make('description')
                            ->label('Public Description for Prospects')
                            ->placeholder('Schedule a brief strategic discovery session to align on requirements...')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Active Link (Accepting Bookings)')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Meeting Title')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Sales Rep')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Public URL')
                    ->formatStateUsing(fn ($state): string => route('focal.meetings.show', $state, false))
                    ->color('primary')
                    ->url(fn (SalesMeetingLink $record): string => route('focal.meetings.show', ['slug' => $record->slug]), shouldOpenInNewTab: true),
                TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state): string => $state.' min')
                    ->badge(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesMeetingLinks::route('/'),
            'create' => CreateSalesMeetingLink::route('/create'),
            'edit' => EditSalesMeetingLink::route('/{record}/edit'),
        ];
    }
}
