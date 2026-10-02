<?php

declare(strict_types=1);

namespace Odden\Filament\Resources;

use BackedEnum;
use Filament\Actions\Action;
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
use Odden\Filament\Resources\LandingPageResource\Pages\CreateLandingPage;
use Odden\Filament\Resources\LandingPageResource\Pages\EditLandingPage;
use Odden\Filament\Resources\LandingPageResource\Pages\ListLandingPages;
use Odden\Marketing\Models\LandingPage;
use UnitEnum;

class LandingPageResource extends Resource
{
    protected static ?string $model = LandingPage::class;

    protected static ?string $modelLabel = 'Landing Page';

    protected static ?string $pluralModelLabel = 'Landing Pages';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::GlobeAlt;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Page Settings')
                    ->schema([
                        TextInput::make('title')
                            ->label('Internal Title')
                            ->placeholder('e.g. Q1 SaaS Enterprise Upgrade')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('URL Slug (/p/{slug})')
                            ->placeholder('q1-saas-upgrade')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Toggle::make('is_published')
                            ->label('Published & Live')
                            ->default(false),
                        Select::make('form_id')
                            ->label('Embedded Lead Capture Form')
                            ->relationship('form', 'title')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Hero & Page Content')
                    ->schema([
                        TextInput::make('headline')
                            ->label('Hero Headline')
                            ->placeholder('Modern Revenue Infrastructure for High-Growth Teams')
                            ->columnSpanFull(),
                        TextInput::make('subheadline')
                            ->label('Subheadline')
                            ->placeholder('Unify your sales, marketing, and customer care in one lightning-fast platform.')
                            ->columnSpanFull(),
                        Textarea::make('body_content')
                            ->label('Body Content (HTML / Markdown)')
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),

                Section::make('SEO & Social Sharing Metadata')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->placeholder('Page Title | Odden CRM'),
                        TextInput::make('og_image_url')
                            ->label('OpenGraph Social Share Image URL')
                            ->placeholder('https://yourdomain.com/og-image.png')
                            ->url(),
                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
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
                TextColumn::make('slug')
                    ->label('Public URL')
                    ->formatStateUsing(fn (LandingPage $record): string => route('odden.marketing.landing-pages.show', $record->slug, false))
                    ->color('info')
                    ->url(fn (LandingPage $record): string => $record->getPublicUrl(), shouldOpenInNewTab: true),
                TextColumn::make('views_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('submissions_count')
                    ->label('Submissions')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('conversion_rate')
                    ->label('Conversion Rate')
                    ->formatStateUsing(fn (LandingPage $record): string => $record->conversion_rate.'%')
                    ->badge()
                    ->color(fn (LandingPage $record): string => $record->conversion_rate > 5.0 ? 'success' : 'gray'),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('embed')
                    ->label('Embed Snippet')
                    ->icon(Heroicon::CodeBracket)
                    ->color('gray')
                    ->modalHeading('Embed Landing Page')
                    ->modalDescription('Copy and paste this HTML embed snippet into your external website.')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->form([
                        Textarea::make('embed_html')
                            ->label('Iframe Code')
                            ->default(fn (LandingPage $record): string => $record->getEmbedSnippet())
                            ->rows(3)
                            ->readOnly(),
                    ]),
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
            'index' => ListLandingPages::route('/'),
            'create' => CreateLandingPage::route('/create'),
            'edit' => EditLandingPage::route('/{record}/edit'),
        ];
    }
}
