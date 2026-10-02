<?php

declare(strict_types=1);

namespace Odden\Filament\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Odden\Core\Models\Contact;
use Odden\Filament\Resources\MarketingSubscriptionResource\Pages\CreateMarketingSubscription;
use Odden\Filament\Resources\MarketingSubscriptionResource\Pages\ListMarketingSubscriptions;
use Odden\Filament\Support\OddenAuthorization;
use Odden\Marketing\Enums\SubscriptionStatus;
use Odden\Marketing\Models\EmailSuppression;
use Odden\Marketing\Models\MarketingSubscription;
use UnitEnum;

class MarketingSubscriptionResource extends Resource
{
    protected static ?string $model = MarketingSubscription::class;

    protected static ?string $modelLabel = 'Suppression & Subscription';

    protected static ?string $pluralModelLabel = 'Suppression List';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShieldExclamation;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subscription & Suppression Status')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Select::make('contact_id')
                            ->label('Linked CRM Contact (Optional)')
                            ->options(fn (): array => Contact::query()->pluck('email', 'id')->all())
                            ->searchable(),
                        Select::make('status')
                            ->label('Status')
                            ->options(collect(SubscriptionStatus::cases())->mapWithKeys(
                                fn (SubscriptionStatus $status) => [$status->value => $status->getLabel()]
                            ))
                            ->default(SubscriptionStatus::Unsubscribed->value)
                            ->required(),
                        DateTimePicker::make('unsubscribed_at')
                            ->label('Suppressed / Unsubscribed At')
                            ->default(now()),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->label('Email Address')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact.full_name')
                    ->label('CRM Contact')
                    ->placeholder('Anonymous / Non-CRM')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?SubscriptionStatus $state): string => $state?->getLabel() ?? '')
                    ->color(fn (?SubscriptionStatus $state): string => $state?->getColor() ?? 'gray')
                    ->sortable(),
                TextColumn::make('unsubscribed_at')
                    ->label('Opt-Out Date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('First Recorded')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(SubscriptionStatus::cases())->mapWithKeys(
                        fn (SubscriptionStatus $status) => [$status->value => $status->getLabel()]
                    )),
            ])
            ->actions([
                Action::make('resubscribe')
                    ->label('Restore / Resubscribe')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->visible(fn (MarketingSubscription $record): bool => $record->status !== SubscriptionStatus::Subscribed)
                    ->authorize(OddenAuthorization::forRecord('update', self::class))
                    ->requiresConfirmation()
                    ->modalHeading('Lift Suppression')
                    ->modalDescription('Are you sure you want to lift this suppression and allow marketing communications to this address?')
                    ->action(function (MarketingSubscription $record): void {
                        $record->update([
                            'status' => SubscriptionStatus::Subscribed,
                            'unsubscribed_at' => null,
                        ]);

                        EmailSuppression::remove($record->email);

                        Notification::make()
                            ->title('Suppression Lifted')
                            ->body("{$record->email} is now marked as Subscribed.")
                            ->success()
                            ->send();
                    }),
                Action::make('suppress')
                    ->label('Suppress')
                    ->icon(Heroicon::NoSymbol)
                    ->color('danger')
                    ->visible(fn (MarketingSubscription $record): bool => $record->status === SubscriptionStatus::Subscribed)
                    ->authorize(OddenAuthorization::forRecord('update', self::class))
                    ->requiresConfirmation()
                    ->action(function (MarketingSubscription $record): void {
                        $record->update([
                            'status' => SubscriptionStatus::Unsubscribed,
                            'unsubscribed_at' => now(),
                        ]);

                        EmailSuppression::suppress(
                            email: $record->email,
                            reason: 'manual_blocklist',
                            source: 'filament_admin'
                        );

                        Notification::make()
                            ->title('Email Suppressed')
                            ->body("{$record->email} is now suppressed from marketing emails.")
                            ->warning()
                            ->send();
                    }),
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
            'index' => ListMarketingSubscriptions::route('/'),
            'create' => CreateMarketingSubscription::route('/create'),
        ];
    }
}
