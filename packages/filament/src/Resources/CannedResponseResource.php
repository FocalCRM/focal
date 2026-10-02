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
use Odden\Filament\Resources\CannedResponseResource\Pages\CreateCannedResponse;
use Odden\Filament\Resources\CannedResponseResource\Pages\EditCannedResponse;
use Odden\Filament\Resources\CannedResponseResource\Pages\ListCannedResponses;
use Odden\Service\Models\CannedResponse;
use UnitEnum;

class CannedResponseResource extends Resource
{
    protected static ?string $model = CannedResponse::class;

    protected static ?string $modelLabel = 'Canned Response';

    protected static ?string $pluralModelLabel = 'Canned Responses';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChatBubbleBottomCenterText;

    protected static UnitEnum|string|null $navigationGroup = 'Service';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Canned Snippet Details')
                    ->schema([
                        TextInput::make('title')
                            ->label('Macro Title')
                            ->placeholder('e.g. Request Logs & Environment Details')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('shortcut')
                            ->label('Trigger Shortcut')
                            ->placeholder('e.g. !logs or !moreinfo')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        TextInput::make('category')
                            ->label('Category')
                            ->placeholder('e.g. Troubleshooting, Billing, Onboarding')
                            ->default('General')
                            ->required(),
                        Toggle::make('is_shared')
                            ->label('Shared with Entire Support Team')
                            ->default(true),
                        Textarea::make('content')
                            ->label('Response Content')
                            ->placeholder('Hi there, thank you for reaching out...')
                            ->rows(6)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shortcut')
                    ->label('Shortcut')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Title')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_shared')
                    ->label('Shared')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Created By')
                    ->searchable(),
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
            'index' => ListCannedResponses::route('/'),
            'create' => CreateCannedResponse::route('/create'),
            'edit' => EditCannedResponse::route('/{record}/edit'),
        ];
    }
}
