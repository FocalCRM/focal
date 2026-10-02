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
use Illuminate\Support\Str;
use Odden\Core\Support\UserModel;
use Odden\Filament\Resources\KnowledgeArticleResource\Pages\CreateKnowledgeArticle;
use Odden\Filament\Resources\KnowledgeArticleResource\Pages\EditKnowledgeArticle;
use Odden\Filament\Resources\KnowledgeArticleResource\Pages\ListKnowledgeArticles;
use Odden\Service\Models\KnowledgeArticle;
use UnitEnum;

class KnowledgeArticleResource extends Resource
{
    protected static ?string $model = KnowledgeArticle::class;

    protected static ?string $modelLabel = 'Knowledge Article';

    protected static ?string $pluralModelLabel = 'Knowledge Base';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;

    protected static UnitEnum|string|null $navigationGroup = 'Service';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Article Information')
                    ->schema([
                        TextInput::make('title')
                            ->label('Article Title')
                            ->placeholder('e.g. How to configure SSO with Google Workspace')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set): void {
                                if ($operation === 'create' && is_string($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->placeholder('e.g. configure-sso-google')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('category')
                            ->label('Category')
                            ->placeholder('e.g. Authentication, Billing, Getting Started')
                            ->default('General')
                            ->required(),
                        Select::make('user_id')
                            ->label('Author')
                            ->options(fn (): array => UserModel::query()->pluck('name', 'id')->all())
                            ->default(fn (): ?int => auth()->id() !== null ? (int) auth()->id() : null)
                            ->searchable(),
                        Toggle::make('is_published')
                            ->label('Published (Visible to Customers)')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Article Content')
                    ->schema([
                        Textarea::make('body')
                            ->label('Content (Markdown / HTML supported)')
                            ->rows(12)
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable(),
                TextColumn::make('helpful_count')
                    ->label('Helpful Votes')
                    ->formatStateUsing(fn (KnowledgeArticle $record): string => "👍 {$record->helpful_count} / 👎 {$record->not_helpful_count}"),
                TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since()
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
            'index' => ListKnowledgeArticles::route('/'),
            'create' => CreateKnowledgeArticle::route('/create'),
            'edit' => EditKnowledgeArticle::route('/{record}/edit'),
        ];
    }
}
