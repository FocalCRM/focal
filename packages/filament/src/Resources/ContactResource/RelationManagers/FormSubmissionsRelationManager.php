<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\ContactResource\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FormSubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'formSubmissions';

    protected static ?string $title = 'Lead Capture & Form Submissions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('form.title')
                    ->label('Form Title')
                    ->readOnly(),
                TextInput::make('utm_source')
                    ->label('Acquisition Source (UTM)')
                    ->readOnly(),
                TextInput::make('ip_address')
                    ->label('IP Address')
                    ->readOnly(),
                KeyValue::make('form_data')
                    ->label('Submitted Field Values')
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('form.title')
                    ->label('Form')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('utm_source')
                    ->label('UTM Source')
                    ->badge()
                    ->placeholder('Direct / Organic')
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }
}
