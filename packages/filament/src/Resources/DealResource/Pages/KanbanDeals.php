<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\DealResource\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Focal\Filament\Resources\DealResource;
use Focal\Sales\Actions\CalculatePipelineForecastAction;
use Focal\Sales\Models\Deal;
use Focal\Sales\Models\Pipeline;
use Focal\Sales\Models\PipelineStage;
use Illuminate\Database\Eloquent\Collection;

class KanbanDeals extends Page
{
    protected static string $resource = DealResource::class;

    protected static ?string $title = 'Deals Pipeline Board';

    protected static ?string $navigationLabel = 'Pipeline Board';

    protected string $view = 'focal-filament::pages.deal-kanban';

    public ?int $pipelineId = null;

    public function mount(): void
    {
        /** @var Pipeline|null $defaultPipeline */
        $defaultPipeline = Pipeline::query()->default()->first() ?? Pipeline::query()->first();

        $this->pipelineId = $defaultPipeline?->id;
    }

    /**
     * @return Collection<int, Pipeline>
     */
    public function getPipelinesProperty(): Collection
    {
        return Pipeline::query()->orderBy('name')->get();
    }

    /**
     * @return Collection<int, PipelineStage>
     */
    public function getStagesProperty(): Collection
    {
        if (! $this->pipelineId) {
            return new Collection;
        }

        return PipelineStage::query()
            ->where('pipeline_id', $this->pipelineId)
            ->with(['deals' => fn ($q) => $q->with(['contacts', 'companies', 'stageHistory', 'stage'])->orderBy('created_at', 'desc')])
            ->orderBy('sort_order')
            ->get();
    }

    public function getTotalPipelineValueProperty(): float
    {
        if (! $this->pipelineId) {
            return 0.0;
        }

        return (float) Deal::query()
            ->where('pipeline_id', $this->pipelineId)
            ->where('status', 'open')
            ->sum('amount');
    }

    /**
     * @return array{
     *     open_value: float,
     *     open_count: int,
     *     weighted_forecast: float,
     *     won_value: float,
     *     won_count: int,
     *     lost_count: int,
     *     win_rate: float,
     *     average_deal_size: float,
     *     lost_reasons: array<string, int>,
     *     stale_deals_count: int
     * }
     */
    public function getForecastProperty(): array
    {
        return app(CalculatePipelineForecastAction::class)->execute($this->pipelineId);
    }

    public function moveDeal(int $dealId, int $stageId): void
    {
        /** @var Deal $deal */
        $deal = Deal::findOrFail($dealId);

        /** @var PipelineStage $stage */
        $stage = PipelineStage::findOrFail($stageId);

        $deal->moveToStage($stage, auth()->id());

        Notification::make()
            ->title('Deal Updated')
            ->body("Moved to [{$stage->name}]")
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('table')
                ->label('Table View')
                ->icon(Heroicon::TableCells)
                ->color('gray')
                ->url(DealResource::getUrl('index')),
            Action::make('create')
                ->label('New Deal')
                ->icon(Heroicon::Plus)
                ->color('primary')
                ->url(DealResource::getUrl('create')),
        ];
    }
}
