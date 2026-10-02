<?php

declare(strict_types=1);

namespace Focal\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Focal\Filament\Pages\Concerns\AuthorizesPageAccess;
use Focal\Filament\Resources\CampaignResource;
use Focal\Filament\Support\FocalAuthorization;
use Focal\Marketing\Actions\GetCampaignAttributionAction;
use Focal\Marketing\Enums\AttributionModel;
use Focal\Marketing\Models\Campaign;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * @property-read Collection<int, Campaign> $availableCampaigns
 * @property-read array<int, array<string, mixed>> $comparedCampaigns
 * @property-read array<string, float> $averages
 */
class CampaignBenchmarking extends Page
{
    use AuthorizesPageAccess;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Scale;

    protected static ?string $navigationLabel = 'Campaign Benchmarking';

    protected static ?string $title = 'Campaign Performance & Multi-Variant Benchmarking';

    protected string $view = 'focal-filament::pages.campaign-benchmarking';

    /**
     * @var list<int|string>
     */
    public array $selectedCampaignIds = [];

    /**
     * @return list<class-string<\Filament\Resources\Resource>>
     */
    protected static function getAuthorizationResources(): array
    {
        return [
            CampaignResource::class,
        ];
    }

    public function mount(): void
    {
        /** @var list<int> $recentIds */
        $recentIds = FocalAuthorization::query(CampaignResource::class, Campaign::class)
            ->where('delivered_count', '>', 0)
            ->orderBy('sent_at', 'desc')
            ->take(4)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if (empty($recentIds)) {
            /** @var list<int> $recentIds */
            $recentIds = FocalAuthorization::query(CampaignResource::class, Campaign::class)->orderBy('created_at', 'desc')->take(3)->pluck('id')->map(fn ($id): int => (int) $id)->all();
        }

        $this->selectedCampaignIds = $recentIds;
    }

    /**
     * @return Collection<int, Campaign>
     */
    public function getAvailableCampaignsProperty(): Collection
    {
        return FocalAuthorization::query(CampaignResource::class, Campaign::class)->orderBy('created_at', 'desc')->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getComparedCampaignsProperty(): array
    {
        if (empty($this->selectedCampaignIds)) {
            return [];
        }

        $campaigns = FocalAuthorization::query(CampaignResource::class, Campaign::class)
            ->whereIn('id', $this->selectedCampaignIds)
            ->get();

        $action = app(GetCampaignAttributionAction::class);
        $result = [];

        foreach ($campaigns as $campaign) {
            $delivered = max(1, $campaign->delivered_count);
            $opens = $campaign->unique_opens_count;
            $clicks = $campaign->unique_clicks_count;
            $unsubs = $campaign->unsubscribes_count;

            $openRate = $campaign->open_rate;
            $clickRate = $campaign->click_rate;
            $ctor = $opens > 0 ? round(($clicks / $opens) * 100, 1) : 0.0;
            $unsubRate = round(($unsubs / $delivered) * 100, 2);

            $attribution = $action->execute($campaign, AttributionModel::Linear);

            $result[] = [
                'campaign' => $campaign,
                'id' => $campaign->id,
                'name' => $campaign->name,
                'subject' => $campaign->subject,
                'topic' => $campaign->topic ?? 'general',
                'delivered' => $campaign->delivered_count,
                'open_rate' => $openRate,
                'click_rate' => $clickRate,
                'ctor' => $ctor,
                'unsub_rate' => $unsubRate,
                'cost' => (float) $campaign->actual_cost,
                'leads' => $attribution['leads_count'],
                'cpl' => $attribution['cost_per_lead'],
                'roi' => $attribution['roi_percentage'],
                'won_revenue' => $attribution['attributed_won_revenue'],
            ];
        }

        return $result;
    }

    /**
     * @return array<string, float>
     */
    public function getAveragesProperty(): array
    {
        $compared = $this->getComparedCampaignsProperty();
        $count = count($compared);
        if ($count === 0) {
            return ['open_rate' => 0.0, 'click_rate' => 0.0, 'ctor' => 0.0, 'unsub_rate' => 0.0];
        }

        $openSum = 0.0;
        $clickSum = 0.0;
        $ctorSum = 0.0;
        $unsubSum = 0.0;

        foreach ($compared as $item) {
            $openSum += (float) $item['open_rate'];
            $clickSum += (float) $item['click_rate'];
            $ctorSum += (float) $item['ctor'];
            $unsubSum += (float) $item['unsub_rate'];
        }

        return [
            'open_rate' => round($openSum / $count, 1),
            'click_rate' => round($clickSum / $count, 1),
            'ctor' => round($ctorSum / $count, 1),
            'unsub_rate' => round($unsubSum / $count, 2),
        ];
    }
}
