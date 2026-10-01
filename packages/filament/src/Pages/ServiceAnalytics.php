<?php

declare(strict_types=1);

namespace Focal\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Focal\Core\Support\UserModel;
use Focal\Service\Enums\TicketPriority;
use Focal\Service\Enums\TicketSource;
use Focal\Service\Enums\TicketStatus;
use Focal\Service\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use UnitEnum;

class ServiceAnalytics extends Page
{
    protected static UnitEnum|string|null $navigationGroup = 'Service';

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBar;

    protected static ?string $navigationLabel = 'Service Analytics';

    protected static ?string $title = 'Support Operations & SLA Analytics';

    protected string $view = 'focal-filament::pages.service-analytics';

    public string $dateRange = '30_days';

    public function setDateRange(string $range): void
    {
        if (in_array($range, ['7_days', '30_days', 'this_month', 'all_time'], true)) {
            $this->dateRange = $range;
        }
    }

    /**
     * @return array{
     *     total_tickets: int,
     *     resolved_tickets: int,
     *     resolution_rate: float,
     *     avg_frt_minutes: ?float,
     *     avg_frt_formatted: string,
     *     avg_mttr_hours: ?float,
     *     avg_mttr_formatted: string,
     *     sla_compliance_rate: float,
     *     sla_breaches_total: int,
     *     csat_average: ?float,
     *     csat_total_ratings: int,
     *     csat_distribution: array<int, int>
     * }
     */
    public function getSummaryStatsProperty(): array
    {
        $query = $this->getBaseQuery();

        $totalTickets = (clone $query)->count();

        $resolvedQuery = (clone $query)->whereIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value]);
        $resolvedTickets = $resolvedQuery->count();

        $resolutionRate = $totalTickets > 0 ? round(($resolvedTickets / $totalTickets) * 100, 1) : 0.0;

        // Calculate Average First Response Time (FRT)
        $respondedTickets = (clone $query)
            ->whereNotNull('first_responded_at')
            ->get(['created_at', 'first_responded_at']);

        $totalFrtMinutes = 0.0;
        foreach ($respondedTickets as $t) {
            if ($t->created_at !== null && $t->first_responded_at !== null) {
                $totalFrtMinutes += abs($t->first_responded_at->diffInMinutes($t->created_at));
            }
        }
        $avgFrtMinutes = $respondedTickets->isNotEmpty() ? round($totalFrtMinutes / $respondedTickets->count(), 1) : null;
        $avgFrtFormatted = $avgFrtMinutes !== null
            ? ($avgFrtMinutes > 60 ? round($avgFrtMinutes / 60, 1).' hrs' : round($avgFrtMinutes).' mins')
            : 'N/A';

        // Calculate Average MTTR
        $resolvedWithDates = (clone $query)
            ->whereNotNull('resolved_at')
            ->get(['created_at', 'resolved_at']);

        $totalMttrHours = 0.0;
        foreach ($resolvedWithDates as $t) {
            if ($t->created_at !== null && $t->resolved_at !== null) {
                $totalMttrHours += abs($t->resolved_at->diffInHours($t->created_at));
            }
        }
        $avgMttrHours = $resolvedWithDates->isNotEmpty() ? round($totalMttrHours / $resolvedWithDates->count(), 1) : null;
        $avgMttrFormatted = $avgMttrHours !== null
            ? ($avgMttrHours > 24 ? round($avgMttrHours / 24, 1).' days' : $avgMttrHours.' hrs')
            : 'N/A';

        // SLA Compliance
        $slaEvaluated = (clone $query)->where(function (Builder $q): void {
            $q->whereNotNull('first_responded_at')->orWhereNotNull('resolved_at');
        })->count();

        $slaBreaches = (clone $query)->where(function (Builder $q): void {
            $q->where('is_sla_response_breached', true)
                ->orWhere('is_sla_resolution_breached', true);
        })->count();

        $slaComplianceRate = $slaEvaluated > 0
            ? round((($slaEvaluated - min($slaBreaches, $slaEvaluated)) / $slaEvaluated) * 100, 1)
            : 100.0;

        // CSAT stats
        $csatTickets = (clone $query)->whereNotNull('csat_rating')->get(['csat_rating']);
        $csatAvg = $csatTickets->isNotEmpty() ? round((float) $csatTickets->avg('csat_rating'), 1) : null;
        $csatTotal = $csatTickets->count();

        $csatDistribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($csatTickets as $t) {
            $val = (int) $t->csat_rating;
            if (isset($csatDistribution[$val])) {
                $csatDistribution[$val]++;
            }
        }

        return [
            'total_tickets' => $totalTickets,
            'resolved_tickets' => $resolvedTickets,
            'resolution_rate' => $resolutionRate,
            'avg_frt_minutes' => $avgFrtMinutes,
            'avg_frt_formatted' => $avgFrtFormatted,
            'avg_mttr_hours' => $avgMttrHours,
            'avg_mttr_formatted' => $avgMttrFormatted,
            'sla_compliance_rate' => $slaComplianceRate,
            'sla_breaches_total' => $slaBreaches,
            'csat_average' => $csatAvg,
            'csat_total_ratings' => $csatTotal,
            'csat_distribution' => $csatDistribution,
        ];
    }

    /**
     * @return array<string, array{label: string, count: int, percentage: float}>
     */
    public function getChannelBreakdownProperty(): array
    {
        $query = $this->getBaseQuery();
        $total = (clone $query)->count();

        $result = [];
        foreach (TicketSource::cases() as $source) {
            $count = (clone $query)->where('source', $source->value)->count();
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0.0;

            $result[$source->value] = [
                'label' => $source->getLabel(),
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        return $result;
    }

    /**
     * @return array<string, array{label: string, count: int, percentage: float}>
     */
    public function getPriorityBreakdownProperty(): array
    {
        $query = $this->getBaseQuery();
        $total = (clone $query)->count();

        $result = [];
        foreach (TicketPriority::cases() as $priority) {
            $count = (clone $query)->where('priority', $priority->value)->count();
            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0.0;

            $result[$priority->value] = [
                'label' => $priority->getLabel(),
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        return $result;
    }

    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     email: string,
     *     assigned_count: int,
     *     resolved_count: int,
     *     breach_count: int,
     *     avg_csat: ?float
     * }>
     */
    public function getAgentPerformanceProperty(): array
    {
        $agents = UserModel::query()->orderBy('name')->get();
        $list = [];

        foreach ($agents as $agent) {
            $query = $this->getBaseQuery()->where('owner_id', $agent->getKey());
            $assigned = (clone $query)->count();
            if ($assigned === 0) {
                continue;
            }

            $resolved = (clone $query)->whereIn('status', [TicketStatus::Resolved->value, TicketStatus::Closed->value])->count();
            $breaches = (clone $query)->where(function (Builder $q): void {
                $q->where('is_sla_response_breached', true)
                    ->orWhere('is_sla_resolution_breached', true);
            })->count();

            $csatAvg = (clone $query)->whereNotNull('csat_rating')->avg('csat_rating');

            $list[] = [
                'id' => $agent->getKey(),
                'name' => UserModel::displayName($agent),
                'email' => $agent->getAttribute('email'),
                'assigned_count' => $assigned,
                'resolved_count' => $resolved,
                'breach_count' => $breaches,
                'avg_csat' => $csatAvg !== null ? round((float) $csatAvg, 1) : null,
            ];
        }

        return $list;
    }

    /**
     * @return Builder<Ticket>
     */
    protected function getBaseQuery(): Builder
    {
        $query = Ticket::query();

        return match ($this->dateRange) {
            '7_days' => $query->where('created_at', '>=', now()->subDays(7)),
            '30_days' => $query->where('created_at', '>=', now()->subDays(30)),
            'this_month' => $query->where('created_at', '>=', Carbon::now()->startOfMonth()),
            default => $query,
        };
    }
}
