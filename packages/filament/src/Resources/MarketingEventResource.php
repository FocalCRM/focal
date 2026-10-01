<?php

declare(strict_types=1);

namespace Focal\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
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
use Focal\Filament\Resources\MarketingEventResource\Pages\CreateMarketingEvent;
use Focal\Filament\Resources\MarketingEventResource\Pages\EditMarketingEvent;
use Focal\Filament\Resources\MarketingEventResource\Pages\ListMarketingEvents;
use Focal\Marketing\Models\MarketingEvent;
use UnitEnum;

class MarketingEventResource extends Resource
{
    protected static ?string $model = MarketingEvent::class;

    protected static ?string $modelLabel = 'Marketing Event';

    protected static ?string $pluralModelLabel = 'Marketing Events';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event Strategy & Scheduling')
                    ->schema([
                        TextInput::make('title')
                            ->label('Event Title')
                            ->placeholder('e.g. Q4 Executive Product Launch & Live AMA')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->placeholder('q4-product-launch-ama')
                            ->required()
                            ->maxLength(255),
                        Select::make('event_type')
                            ->label('Format')
                            ->options([
                                'webinar' => 'Virtual Webinar',
                                'workshop' => 'Live Interactive Workshop',
                                'round_table' => 'Executive Roundtable',
                                'in_person' => 'In-Person Summit / Meetup',
                            ])
                            ->default('webinar')
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'live' => 'Live in Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'draft' => 'Draft',
                            ])
                            ->default('scheduled')
                            ->required(),
                        DateTimePicker::make('starts_at')
                            ->label('Start Date & Time'),
                        DateTimePicker::make('ends_at')
                            ->label('End Date & Time'),
                        TextInput::make('virtual_meeting_url')
                            ->label('Virtual Meeting Link (Zoom / Teams / Meet)')
                            ->url()
                            ->placeholder('https://zoom.us/j/...'),
                        TextInput::make('location')
                            ->label('Physical Venue / Location (if in-person)')
                            ->placeholder('e.g. San Francisco, CA'),
                        TextInput::make('capacity')
                            ->label('Maximum Attendee Capacity')
                            ->numeric()
                            ->placeholder('e.g. 500'),
                        Toggle::make('is_published')
                            ->label('Publicly Published & Accepting Registrations')
                            ->default(true),
                        Textarea::make('description')
                            ->label('Agenda & Speaker Notes')
                            ->placeholder('Describe the key takeaways, guest speakers, and audience benefits...')
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
                TextColumn::make('title')
                    ->label('Event Title')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event_type')
                    ->label('Format')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'live' => 'danger',
                        'scheduled' => 'success',
                        'completed' => 'gray',
                        'cancelled' => 'warning',
                        default => 'secondary',
                    })
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Scheduled Date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                TextColumn::make('registrations_count')
                    ->label('Registered')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('attendees_count')
                    ->label('Attended')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('attendance_rate')
                    ->label('Turnout %')
                    ->getStateUsing(fn (MarketingEvent $record): string => $record->attendanceRate().'%')
                    ->badge()
                    ->color(fn (MarketingEvent $record): string => match (true) {
                        $record->attendanceRate() >= 50 => 'success',
                        $record->attendanceRate() >= 25 => 'warning',
                        default => 'gray',
                    }),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
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
            'index' => ListMarketingEvents::route('/'),
            'create' => CreateMarketingEvent::route('/create'),
            'edit' => EditMarketingEvent::route('/{record}/edit'),
        ];
    }
}
