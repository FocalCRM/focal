<?php

declare(strict_types=1);

namespace Focal\Filament\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Focal\Core\Models\Contact;
use Focal\Filament\Resources\SalesSequenceResource\Pages\CreateSalesSequence;
use Focal\Filament\Resources\SalesSequenceResource\Pages\EditSalesSequence;
use Focal\Filament\Resources\SalesSequenceResource\Pages\ListSalesSequences;
use Focal\Sales\Actions\EnrollContactInSequenceAction;
use Focal\Sales\Models\SalesEmailTemplate;
use Focal\Sales\Models\SalesSequence;
use UnitEnum;

class SalesSequenceResource extends Resource
{
    protected static ?string $model = SalesSequence::class;

    protected static ?string $modelLabel = 'Cadence';

    protected static ?string $pluralModelLabel = 'Sales Cadences';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowPath;

    protected static UnitEnum|string|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cadence Overview')
                    ->schema([
                        TextInput::make('name')
                            ->label('Cadence Name')
                            ->placeholder('e.g. Enterprise Inbound Follow-Up')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Active Cadence')
                            ->default(true),
                        Textarea::make('description')
                            ->label('Description & Purpose')
                            ->placeholder('Multi-channel 4-touch cadence for marketing qualified leads...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Sequence Steps')
                    ->schema([
                        Repeater::make('steps')
                            ->label('Sequential Actions')
                            ->schema([
                                TextInput::make('step')
                                    ->label('Step #')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                                Select::make('type')
                                    ->label('Action Type')
                                    ->options([
                                        'email' => 'Automated Email',
                                        'call' => 'Outbound Phone Call',
                                        'linkedin' => 'LinkedIn Social Touch',
                                        'task' => 'General Sales Task',
                                    ])
                                    ->default('email')
                                    ->required(),
                                TextInput::make('delay_days')
                                    ->label('Delay (days after prior step)')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                                TextInput::make('title')
                                    ->label('Step Summary')
                                    ->placeholder('e.g. Initial intro email')
                                    ->required(),
                                Select::make('template_id')
                                    ->label('Email Template')
                                    ->options(fn (): array => SalesEmailTemplate::query()->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->placeholder('Select template...')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->defaultItems(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Cadence')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('steps_count')
                    ->label('Steps')
                    ->getStateUsing(fn (SalesSequence $record): int => $record->totalSteps())
                    ->badge()
                    ->color('primary'),
                TextColumn::make('enrolled_count')
                    ->label('Active Contacts')
                    ->getStateUsing(fn (SalesSequence $record): int => $record->enrollments()->where('status', 'active')->count())
                    ->badge()
                    ->color('success'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('enrollContact')
                    ->label('Enroll Contact')
                    ->icon(Heroicon::UserPlus)
                    ->color('primary')
                    ->modalHeading(fn (SalesSequence $record): string => "Enroll Contact in {$record->name}")
                    ->form([
                        Select::make('contact_id')
                            ->label('Select Contact')
                            ->options(fn (): array => Contact::query()->limit(100)->pluck('first_name', 'id')->map(function ($name, $id): string {
                                $c = Contact::find($id);

                                return "{$name} {$c?->last_name} ({$c?->email})";
                            })->all())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (SalesSequence $record, array $data): void {
                        /** @var Contact $contact */
                        $contact = Contact::findOrFail($data['contact_id']);
                        app(EnrollContactInSequenceAction::class)->execute($contact, $record);

                        Notification::make()
                            ->title('Contact Enrolled')
                            ->body("{$contact->full_name} enrolled in {$record->name}.")
                            ->success()
                            ->send();
                    }),
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
            'index' => ListSalesSequences::route('/'),
            'create' => CreateSalesSequence::route('/create'),
            'edit' => EditSalesSequence::route('/{record}/edit'),
        ];
    }
}
