<?php

declare(strict_types=1);

namespace Odden\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Odden\Filament\Pages\Concerns\AuthorizesPageAccess;
use Odden\Filament\Resources\CampaignResource;
use Odden\Marketing\Actions\GetCampaignAttributionAction;
use Odden\Marketing\Enums\AttributionModel;
use Odden\Marketing\Models\Campaign;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * @property-read array<int, array<string, mixed>> $campaignsData
 * @property-read float $totalBudget
 * @property-read float $totalCost
 * @property-read float $totalAttributedWon
 * @property-read float $totalAttributedPipeline
 * @property-read float $overallRoi
 * @property-read int $totalLeads
 * @property-read float $blendedCpl
 */
class MarketingAttribution extends Page
{
    use AuthorizesPageAccess;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBarSquare;

    protected static ?string $navigationLabel = 'Attribution & ROI';

    protected static ?string $title = 'Marketing Multi-Touch Attribution & Campaign ROI';

    protected string $view = 'odden-filament::pages.marketing-attribution';

    public string $selectedModel = 'first_touch';

    /**
     * @return list<class-string<\Filament\Resources\Resource>>
     */
    protected static function getAuthorizationResources(): array
    {
        return [
            CampaignResource::class,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCampaignsDataProperty(): array
    {
        $model = AttributionModel::tryFrom($this->selectedModel) ?? AttributionModel::FirstTouch;
        $action = app(GetCampaignAttributionAction::class);

        /** @var Collection<int, Campaign> $campaigns */
        $campaigns = Campaign::query()->orderBy('created_at', 'desc')->get();

        $data = [];
        foreach ($campaigns as $campaign) {
            $data[] = array_merge(
                ['campaign' => $campaign],
                $action->execute($campaign, $model)
            );
        }

        return $data;
    }

    public function getTotalBudgetProperty(): float
    {
        return (float) Campaign::query()->sum('budget');
    }

    public function getTotalCostProperty(): float
    {
        return (float) Campaign::query()->sum('actual_cost');
    }

    public function getTotalAttributedWonProperty(): float
    {
        $total = 0.0;
        foreach ($this->getCampaignsDataProperty() as $item) {
            $total += (float) ($item['attributed_won_revenue'] ?? 0.0);
        }

        return round($total, 2);
    }

    public function getTotalAttributedPipelineProperty(): float
    {
        $total = 0.0;
        foreach ($this->getCampaignsDataProperty() as $item) {
            $total += (float) ($item['attributed_pipeline_value'] ?? 0.0);
        }

        return round($total, 2);
    }

    public function getOverallRoiProperty(): float
    {
        $cost = $this->getTotalCostProperty();
        if ($cost <= 0.0) {
            return 0.0;
        }

        $won = $this->getTotalAttributedWonProperty();
        $net = $won - $cost;

        return round(($net / $cost) * 100, 1);
    }

    public function getTotalLeadsProperty(): int
    {
        $total = 0;
        foreach ($this->getCampaignsDataProperty() as $item) {
            $total += (int) ($item['leads_count'] ?? 0);
        }

        return $total;
    }

    public function getBlendedCplProperty(): float
    {
        $leads = $this->getTotalLeadsProperty();
        if ($leads <= 0) {
            return 0.0;
        }

        return round($this->getTotalCostProperty() / $leads, 2);
    }
}
