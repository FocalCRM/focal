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
use Illuminate\Contracts\View\View;
use Odden\Filament\Resources\MarketingWorkflowResource\Pages\CreateMarketingWorkflow;
use Odden\Filament\Resources\MarketingWorkflowResource\Pages\EditMarketingWorkflow;
use Odden\Filament\Resources\MarketingWorkflowResource\Pages\ListMarketingWorkflows;
use Odden\Filament\Resources\MarketingWorkflowResource\RelationManagers\StepsRelationManager;
use Odden\Marketing\Enums\WorkflowTriggerType;
use Odden\Marketing\Models\MarketingWorkflow;
use UnitEnum;

class MarketingWorkflowResource extends Resource
{
    protected static ?string $model = MarketingWorkflow::class;

    protected static ?string $modelLabel = 'Marketing Workflow';

    protected static ?string $pluralModelLabel = 'Marketing Workflows';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowPath;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Workflow Strategy & Enrollment Triggers')
                    ->schema([
                        TextInput::make('name')
                            ->label('Workflow Name')
                            ->placeholder('e.g. 5-Day New Lead Welcome Journey')
                            ->required()
                            ->maxLength(255),
                        Select::make('trigger_type')
                            ->label('Enrollment Trigger')
                            ->options(collect(WorkflowTriggerType::cases())->mapWithKeys(fn (WorkflowTriggerType $t): array => [$t->value => $t->label()])->all())
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Active Workflow')
                            ->default(true),
                        Textarea::make('description')
                            ->label('Description & Goals')
                            ->placeholder('Describe the target audience, cadence, and desired conversion outcome...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Workflow')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('trigger_type')
                    ->label('Trigger')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state instanceof WorkflowTriggerType ? $state->label() : (WorkflowTriggerType::tryFrom((string) $state)?->label() ?? (string) $state)),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('enrollments_count')
                    ->label('Enrolled')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('completed_count')
                    ->label('Completed')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->actions([
                Action::make('visualJourney')
                    ->label('Visual Journey')
                    ->icon(Heroicon::Sparkles)
                    ->color('info')
                    ->modalHeading(fn (MarketingWorkflow $record): string => "Workflow Journey: {$record->name}")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (MarketingWorkflow $record): View => view('odden-marketing::workflow-journey', [
                        'workflow' => $record->load('steps'),
                    ])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            StepsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketingWorkflows::route('/'),
            'create' => CreateMarketingWorkflow::route('/create'),
            'edit' => EditMarketingWorkflow::route('/{record}/edit'),
        ];
    }
}
