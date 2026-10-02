<?php

declare(strict_types=1);

namespace Odden\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Odden\Core\Enums\LifecycleStage;
use Odden\Core\Models\Contact;
use Odden\Filament\Pages\Concerns\AuthorizesPageAccess;
use Odden\Filament\Resources\CampaignResource;
use Odden\Filament\Resources\ContactResource;
use Odden\Filament\Resources\MarketingFormResource;
use Odden\Filament\Resources\MarketingWorkflowResource;
use Odden\Filament\Support\OddenAuthorization;
use Odden\Marketing\Actions\AnalyzeConversionFunnelAction;
use Odden\Marketing\Actions\CalculateClosedLoopMetricsAction;
use Odden\Marketing\Actions\DispatchCampaignAction;
use Odden\Marketing\Enums\CampaignStatus;
use Odden\Marketing\Exceptions\CampaignHasNoAudienceException;
use Odden\Marketing\Models\Campaign;
use Odden\Marketing\Models\FormSubmission;
use Odden\Marketing\Models\MarketingForm;
use Odden\Marketing\Models\MarketingWorkflow;
use UnitEnum;

class MarketingCockpit extends Page
{
    use AuthorizesPageAccess;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 0;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Megaphone;

    protected static ?string $navigationLabel = 'Marketing Cockpit';

    protected static ?string $title = 'Marketing Campaigns & Lead Acquisition';

    protected string $view = 'odden-filament::pages.marketing-cockpit';

    /**
     * @return list<class-string<\Filament\Resources\Resource>>
     */
    protected static function getAuthorizationResources(): array
    {
        return [
            CampaignResource::class,
            MarketingFormResource::class,
            MarketingWorkflowResource::class,
            ContactResource::class,
        ];
    }

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
        $campaign = OddenAuthorization::findAndAuthorize(CampaignResource::class, Campaign::class, $campaignId, 'update');

        try {
            $results = app(DispatchCampaignAction::class)->execute($campaign);
        } catch (CampaignHasNoAudienceException) {
            Notification::make()
                ->title('Campaign Has No Audience')
                ->body('Choose a list for this campaign before sending it.')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Campaign Broadcast Sent')
            ->body("Delivered to {$results['delivered_count']} recipients ({$results['suppressed_count']} suppressed).")
            ->success()
            ->send();
    }
}
