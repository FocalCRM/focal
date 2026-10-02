<?php

declare(strict_types=1);

namespace Odden\Filament\Pages;

use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Odden\Filament\Pages\Concerns\AuthorizesPageAccess;
use Odden\Filament\Resources\CampaignResource;
use Odden\Marketing\Models\Campaign;
use UnitEnum;

/**
 * @property-read array<int, Collection<int, Campaign>> $campaignsByDay
 * @property-read Carbon $currentDate
 */
class MarketingCalendar extends Page
{
    use AuthorizesPageAccess;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static ?string $navigationLabel = 'Campaign Calendar';

    protected static ?string $title = 'Campaign Broadcast & Delivery Schedule';

    protected string $view = 'odden-filament::pages.marketing-calendar';

    public int $year;

    public int $month;

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
        $this->year = (int) now()->year;
        $this->month = (int) now()->month;
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->subMonth();
        $this->year = (int) $date->year;
        $this->month = (int) $date->month;
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->addMonth();
        $this->year = (int) $date->year;
        $this->month = (int) $date->month;
    }

    public function currentMonth(): void
    {
        $this->year = (int) now()->year;
        $this->month = (int) now()->month;
    }

    public function getCurrentDateProperty(): Carbon
    {
        return Carbon::createFromDate($this->year, $this->month, 1);
    }

    /**
     * @return array<int, Collection<int, Campaign>>
     */
    public function getCampaignsByDayProperty(): array
    {
        $start = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        /** @var Collection<int, Campaign> $campaigns */
        $campaigns = Campaign::query()
            ->where(function ($q) use ($start, $end): void {
                $q->whereBetween('scheduled_at', [$start, $end])
                    ->orWhereBetween('sent_at', [$start, $end])
                    ->orWhere(function ($sub) use ($start, $end): void {
                        $sub->whereNull('scheduled_at')
                            ->whereNull('sent_at')
                            ->whereBetween('created_at', [$start, $end]);
                    });
            })
            ->get();

        $byDay = [];
        $daysInMonth = $start->daysInMonth;
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $byDay[$i] = new Collection;
        }

        foreach ($campaigns as $campaign) {
            $date = $campaign->scheduled_at ?? $campaign->sent_at ?? $campaign->created_at;
            if ($date !== null && (int) $date->year === $this->year && (int) $date->month === $this->month) {
                $day = (int) $date->day;
                if (isset($byDay[$day])) {
                    $byDay[$day]->push($campaign);
                }
            }
        }

        return $byDay;
    }
}
