<?php

declare(strict_types=1);

namespace Odden\Core\Actions;

use Carbon\CarbonInterface;
use Odden\Core\Enums\LifecycleStage;
use Odden\Core\Models\LifecycleStageTransition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CalculateFunnelVelocityAction
{
    /**
     * Calculate lifecycle stage velocity and conversion timing metrics across transitions.
     *
     * @param  class-string<Model>  $recordClass
     * @return array{
     *     total_transitions: int,
     *     average_duration_seconds: float,
     *     average_duration_days: float,
     *     median_duration_days: float,
     *     min_duration_days: float,
     *     max_duration_days: float,
     *     transitions_by_stage: list<array{
     *         from_stage: string|null,
     *         to_stage: string,
     *         count: int,
     *         avg_days: float,
     *         formatted_avg_duration: string
     *     }>
     * }
     */
    public function execute(
        string $recordClass,
        ?LifecycleStage $fromStage = null,
        ?LifecycleStage $toStage = null,
        ?CarbonInterface $startDate = null,
        ?CarbonInterface $endDate = null,
        ?int $teamId = null
    ): array {
        /** @var Model $instance */
        $instance = new $recordClass;
        $morphClass = $instance->getMorphClass();

        $query = LifecycleStageTransition::query()
            ->where('record_type', $morphClass);

        if ($fromStage !== null) {
            $query->where('from_stage', $fromStage->value);
        }

        if ($toStage !== null) {
            $query->where('to_stage', $toStage->value);
        }

        if ($startDate !== null) {
            $query->where('transitioned_at', '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->where('transitioned_at', '<=', $endDate);
        }

        if ($teamId !== null) {
            $query->where('team_id', $teamId);
        }

        /** @var Collection<int, LifecycleStageTransition> $transitions */
        $transitions = $query->get();

        $totalTransitions = $transitions->count();

        if ($totalTransitions === 0) {
            return [
                'total_transitions' => 0,
                'average_duration_seconds' => 0.0,
                'average_duration_days' => 0.0,
                'median_duration_days' => 0.0,
                'min_duration_days' => 0.0,
                'max_duration_days' => 0.0,
                'transitions_by_stage' => [],
            ];
        }

        $durations = $transitions
            ->pluck('duration_seconds')
            ->filter(fn ($val) => $val !== null)
            ->map(fn ($val) => (int) $val)
            ->sort()
            ->values();

        $avgSeconds = $durations->count() > 0 ? (float) $durations->avg() : 0.0;
        $avgDays = round($avgSeconds / 86400, 2);

        $minSeconds = $durations->min() ?? 0;
        $maxSeconds = $durations->max() ?? 0;

        $medianSeconds = 0.0;
        $count = $durations->count();
        if ($count > 0) {
            $middle = (int) floor($count / 2);
            if ($count % 2 === 0) {
                $medianSeconds = ((float) $durations[$middle - 1] + (float) $durations[$middle]) / 2;
            } else {
                $medianSeconds = (float) $durations[$middle];
            }
        }
        $medianDays = round($medianSeconds / 86400, 2);

        // Group transitions by from -> to
        $transitionsByStage = [];
        $grouped = $transitions->groupBy(fn (LifecycleStageTransition $t) => ($t->from_stage !== null ? $t->from_stage->value : 'null').'->'.$t->to_stage->value);

        foreach ($grouped as $group) {
            /** @var LifecycleStageTransition $first */
            $first = $group->first();
            $groupDurations = $group->pluck('duration_seconds')->filter(fn ($val) => $val !== null)->map(fn ($val) => (int) $val);
            $groupAvgSeconds = $groupDurations->count() > 0 ? (float) $groupDurations->avg() : 0.0;
            $groupAvgDays = round($groupAvgSeconds / 86400, 2);

            $formatted = $groupAvgDays >= 1
                ? $groupAvgDays.' days'
                : (round($groupAvgSeconds / 3600, 1).' hours');

            $transitionsByStage[] = [
                'from_stage' => $first->from_stage?->value,
                'to_stage' => $first->to_stage->value,
                'count' => $group->count(),
                'avg_days' => $groupAvgDays,
                'formatted_avg_duration' => $formatted,
            ];
        }

        return [
            'total_transitions' => $totalTransitions,
            'average_duration_seconds' => round($avgSeconds, 1),
            'average_duration_days' => $avgDays,
            'median_duration_days' => $medianDays,
            'min_duration_days' => round($minSeconds / 86400, 2),
            'max_duration_days' => round($maxSeconds / 86400, 2),
            'transitions_by_stage' => $transitionsByStage,
        ];
    }
}
