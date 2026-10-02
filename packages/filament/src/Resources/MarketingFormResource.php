<?php

declare(strict_types=1);

namespace Odden\Filament\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
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
use Odden\Filament\Resources\MarketingFormResource\Pages\CreateMarketingForm;
use Odden\Filament\Resources\MarketingFormResource\Pages\EditMarketingForm;
use Odden\Filament\Resources\MarketingFormResource\Pages\ListMarketingForms;
use Odden\Marketing\Models\MarketingForm;
use UnitEnum;

class MarketingFormResource extends Resource
{
    protected static ?string $model = MarketingForm::class;

    protected static ?string $modelLabel = 'Lead Capture Form';

    protected static ?string $pluralModelLabel = 'Lead Capture Forms';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::RectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Form Configuration')
                    ->schema([
                        TextInput::make('title')
                            ->label('Form Title')
                            ->placeholder('e.g. Schedule a Product Consultation')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->placeholder('schedule-consultation')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->label('Introductory Description')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('submit_button_text')
                            ->label('Button Text')
                            ->default('Submit')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Active & Accepting Submissions')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Post-Submission Actions')
                    ->schema([
                        TextInput::make('success_message')
                            ->label('Success Confirmation Message')
                            ->placeholder('Thank you! Our sales team will get back to you within 24 hours.')
                            ->default('Thank you for your submission!'),
                        TextInput::make('redirect_url')
                            ->label('Optional Redirect URL (overrides success message)')
                            ->placeholder('https://yourdomain.com/thank-you')
                            ->url(),
                    ])
                    ->columns(2),

                Section::make('Form Fields Specification')
                    ->schema([
                        KeyValue::make('fields_schema')
                            ->label('Fields Definition (JSON array or field list)')
                            ->helperText('Pre-configured fields: first_name, last_name, email, phone, company')
                            ->columnSpanFull(),
                    ]),

                Section::make('Progressive Profiling & Smart Forms')
                    ->description('Automatically replace known contact fields with progressive qualification questions for returning visitors.')
                    ->schema([
                        Toggle::make('progressive_profiling_enabled')
                            ->label('Enable Progressive Profiling')
                            ->helperText('When enabled, already known contact information (e.g. name, email) is replaced with progressive questions.')
                            ->default(false),
                        KeyValue::make('progressive_fields')
                            ->label('Progressive Fields Queue')
                            ->helperText('Field key and label to ask returning contacts (e.g. budget => Estimated Annual Budget, timeline => Purchase Horizon).')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Form Title')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Public URL')
                    ->formatStateUsing(fn (MarketingForm $record): string => route('odden.marketing.forms.show', $record->slug, false))
                    ->color('info')
                    ->url(fn (MarketingForm $record): string => $record->getPublicUrl(), shouldOpenInNewTab: true),
                TextColumn::make('submissions_count')
                    ->label('Submissions')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('progressive_profiling_enabled')
                    ->label('Smart')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('embed')
                    ->label('Embed Code')
                    ->icon(Heroicon::CodeBracket)
                    ->color('gray')
                    ->modalHeading('Embed Lead Capture Form')
                    ->modalDescription('Copy and paste this HTML embed snippet into your marketing landing page or website.')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->form([
                        Textarea::make('embed_html')
                            ->label('Embed Code (Iframe)')
                            ->default(fn (MarketingForm $record): string => '<iframe src="'.$record->getPublicUrl().'" width="100%" height="600" frameborder="0"></iframe>')
                            ->rows(4)
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
            'index' => ListMarketingForms::route('/'),
            'create' => CreateMarketingForm::route('/create'),
            'edit' => EditMarketingForm::route('/{record}/edit'),
        ];
    }
}
