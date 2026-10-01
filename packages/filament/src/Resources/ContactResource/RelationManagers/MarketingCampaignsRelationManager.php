<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\ContactResource\RelationManagers;

use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Focal\Marketing\Enums\RecipientStatus;

class MarketingCampaignsRelationManager extends RelationManager
{
    protected static string $relationship = 'campaignRecipients';

    protected static ?string $title = 'Marketing Campaigns & Email Touchpoints';

    protected static string|BackedEnum|null $icon = Heroicon::Megaphone;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Recipient Email')
                    ->readOnly(),
                TextInput::make('status')
                    ->label('Delivery Status')
                    ->readOnly(),
                DateTimePicker::make('sent_at')
                    ->label('Sent Date')
                    ->readOnly(),
                DateTimePicker::make('opened_at')
                    ->label('Opened At')
                    ->readOnly(),
                DateTimePicker::make('clicked_at')
                    ->label('Clicked At')
                    ->readOnly(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('campaign.name')
                    ->label('Campaign')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?RecipientStatus $state): string => $state?->getLabel() ?? '')
                    ->color(fn (?RecipientStatus $state): string => $state?->getColor() ?? 'gray'),

                TextColumn::make('variant')
                    ->label('Variant')
                    ->badge()
                    ->placeholder('Standard')
                    ->color('info'),

                IconColumn::make('opened_at')
                    ->label('Opened')
                    ->boolean()
                    ->trueIcon(Heroicon::Eye)
                    ->falseIcon(Heroicon::Minus)
                    ->trueColor('success')
                    ->falseColor('gray'),

                IconColumn::make('clicked_at')
                    ->label('Clicked')
                    ->boolean()
                    ->trueIcon(Heroicon::CursorArrowRays)
                    ->falseIcon(Heroicon::Minus)
                    ->trueColor('primary')
                    ->falseColor('gray'),

                TextColumn::make('sent_at')
                    ->label('Sent')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }
}
