<?php

declare(strict_types=1);

namespace Focal\Core\Traits;

use Focal\Core\Models\LifecycleStageTransition;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasLifecycleStageTransitions
{
    /**
     * Get all lifecycle stage transitions for this record.
     *
     * @return MorphMany<LifecycleStageTransition, $this>
     */
    public function lifecycleTransitions(): MorphMany
    {
        return $this->morphMany(
            LifecycleStageTransition::class,
            'record'
        )->orderBy('transitioned_at', 'desc');
    }

    /**
     * Get the most recent lifecycle stage transition.
     */
    public function latestLifecycleTransition(): ?LifecycleStageTransition
    {
        return $this->lifecycleTransitions()->first();
    }

    /**
     * Get time spent in the current lifecycle stage in seconds.
     */
    public function timeInCurrentStageSeconds(): int
    {
        $latest = $this->latestLifecycleTransition();

        $referenceTime = $latest !== null
            ? $latest->transitioned_at
            : ($this->created_at ?? now());

        return max(0, abs((int) now()->diffInSeconds($referenceTime)));
    }

    /**
     * Get time spent in the current lifecycle stage in days.
     */
    public function timeInCurrentStageDays(): float
    {
        return round($this->timeInCurrentStageSeconds() / 86400, 2);
    }

    /**
     * Get formatted time spent in the current lifecycle stage.
     */
    public function formattedTimeInCurrentStage(): string
    {
        $seconds = $this->timeInCurrentStageSeconds();

        if ($seconds < 60) {
            return '< 1 min';
        }

        $days = (int) floor($seconds / 86400);
        $hours = (int) floor(($seconds % 86400) / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);

        if ($days > 0) {
            return $days === 1 ? '1 day' : "{$days} days";
        }

        if ($hours > 0) {
            return $hours === 1 ? '1 hour' : "{$hours} hours";
        }

        return $minutes <= 1 ? '1 min' : "{$minutes} mins";
    }
}
