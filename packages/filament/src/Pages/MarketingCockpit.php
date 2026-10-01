<?php

declare(strict_types=1);

namespace Focal\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Focal\Core\Enums\LifecycleStage;
use Focal\Core\Models\Contact;
use Focal\Marketing\Actions\AnalyzeConversionFunnelAction;
use Focal\Marketing\Actions\CalculateClosedLoopMetricsAction;
use Focal\Marketing\Actions\DispatchCampaignAction;
use Focal\Marketing\Enums\CampaignStatus;
use Focal\Marketing\Models\Campaign;
use Focal\Marketing\Models\FormSubmission;
use Focal\Marketing\Models\MarketingForm;
use Focal\Marketing\Models\MarketingWorkflow;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class MarketingCockpit extends Page
{
    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 0;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Megaphone;

    protected static ?string $navigationLabel = 'Marketing Cockpit';

    protected static ?string $title = 'Marketing Campaigns & Lead Acquisition';

    protected string $view = 'focal-filament::pages.marketing-cockpit';

    public function getTotalCampaignsCountProperty(): int
    {
        return Campaign::query()->where('status', CampaignStatus::Sent->value)->count();
    }

    public function getTotalDeliveredProperty(): int
    {
        return (int) Campaign::query()->sum('delivered_count');
    }

    public function getAverageOpenRateProperty(): float
    {
        $delivered = $this->getTotalDeliveredProperty();
        if ($delivered === 0) {
            return 0.0;
        }

        $uniqueOpens = (int) Campaign::query()->sum('unique_opens_count');

        return round(($uniqueOpens / $delivered) * 100, 1);
    }

    public function getAverageClickRateProperty(): float
    {
        $delivered = $this->getTotalDeliveredProperty();
        if ($delivered === 0) {
            return 0.0;
        }

        $uniqueClicks = (int) Campaign::query()->sum('unique_clicks_count');

        return round(($uniqueClicks / $delivered) * 100, 1);
    }

    public function getTotalLeadsCapturedProperty(): int
    {
        return (int) FormSubmission::query()->count();
    }

    public function getActiveWorkflowsCountProperty(): int
    {
        return MarketingWorkflow::query()->where('is_active', true)->count();
    }

    public function getQualifiedLeadsCountProperty(): int
    {
        return Contact::query()
            ->whereIn('lifecycle_stage', [LifecycleStage::MarketingQualifiedLead->value, LifecycleStage::SalesQualifiedLead->value])
            ->count();
    }

    public function getAbTestsRunningCountProperty(): int
    {
        return Campaign::query()
            ->where('is_ab_test', true)
            ->whereNull('ab_winner_variant')
            ->count();
    }

    /**
     * @return Collection<int, Campaign>
     */
    public function getRecentCampaignsProperty(): Collection
    {
        return Campaign::query()
            ->with(['template', 'list'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * @return Collection<int, MarketingForm>
     */
    public function getActiveFormsProperty(): Collection
    {
        return MarketingForm::query()
            ->where('is_active', true)
            ->orderBy('submissions_count', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * @return Collection<int, FormSubmission>
     */
    public function getRecentSubmissionsProperty(): Collection
    {
        return FormSubmission::query()
            ->with(['form', 'contact'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * @return array{
     *     total_influenced_pipeline: float,
     *     total_closed_won_revenue: float,
     *     total_marketing_spend: float,
     *     blended_cac: float,
     *     cost_per_lead: float,
     *     marketing_roi_percentage: float,
     *     won_deals_count: int,
     *     open_deals_count: int,
     *     marketing_win_rate: float,
     *     average_sales_cycle_days: float,
     *     top_campaigns: array<int, array{name: string, won_revenue: float, pipeline_influenced: float, spend: float, roi_percentage: float}>
     * }
     */
    public function getClosedLoopMetricsProperty(): array
    {
        return app(CalculateClosedLoopMetricsAction::class)->execute();
    }

    /**
     * @return array{
     *     steps: list<array{
     *         index: int,
     *         name: string,
     *         type: string,
     *         count: int,
     *         conversion_rate: float,
     *         dropoff_count: int,
     *         dropoff_rate: float,
     *         overall_conversion_rate: float
     *     }>,
     *     total_top_of_funnel: int,
     *     total_bottom_of_funnel: int,
     *     overall_funnel_conversion_rate: float,
     *     time_window_days: int
     * }
     */
    public function getConversionFunnelProperty(): array
    {
        return app(AnalyzeConversionFunnelAction::class)->execute([
            ['name' => 'Web Page Views', 'type' => 'page_view'],
            ['name' => 'Form Submissions', 'type' => 'form_submission'],
            ['name' => 'Product Events', 'type' => 'behavioral_event'],
            ['name' => 'Closed-Won Deals', 'type' => 'deal_won'],
        ]);
    }

    public function sendCampaignNow(int $campaignId): void
    {
        /** @var Campaign|null $campaign */
        $campaign = Campaign::query()->find($campaignId);

        if ($campaign === null) {
            Notification::make()->title('Campaign not found')->danger()->send();

            return;
        }

        $results = (new DispatchCampaignAction)->execute($campaign);

        Notification::make()
            ->title('Campaign Broadcast Sent')
            ->body("Delivered to {$results['delivered_count']} recipients ({$results['suppressed_count']} suppressed).")
            ->success()
            ->send();
    }
}
