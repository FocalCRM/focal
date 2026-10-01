<?php

declare(strict_types=1);

namespace Focal\Core\Models;

use Carbon\CarbonInterface;
use Focal\Core\Enums\LifecycleStage;
use Focal\Core\Support\UserModel;
use Focal\Core\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $record_type
 * @property int $record_id
 * @property LifecycleStage|null $from_stage
 * @property LifecycleStage $to_stage
 * @property int|null $duration_seconds
 * @property string $source
 * @property int|null $user_id
 * @property int|null $team_id
 * @property CarbonInterface $transitioned_at
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read Model $record
 */
class LifecycleStageTransition extends Model
{
    use BelongsToTeam;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'record_type',
        'record_id',
        'from_stage',
        'to_stage',
        'duration_seconds',
        'source',
        'user_id',
        'team_id',
        'transitioned_at',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('focal-core.tables.lifecycle_stage_transitions', 'focal_lifecycle_stage_transitions');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_stage' => LifecycleStage::class,
            'to_stage' => LifecycleStage::class,
            'duration_seconds' => 'integer',
            'transitioned_at' => 'datetime',
        ];
    }

    /**
     * The associated CRM record (Contact, Company, etc.).
     *
     * @return MorphTo<Model, $this>
     */
    public function record(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user who triggered the lifecycle stage change.
     *
     * @return BelongsTo<Model, $this>
     */
    public function user(): BelongsTo
    {
        $userModel = UserModel::className();

        return $this->belongsTo($userModel, 'user_id');
    }

    /**
     * Get elapsed duration in days.
     */
    public function durationInDays(): ?float
    {
        if ($this->duration_seconds === null) {
            return null;
        }

        return round($this->duration_seconds / 86400, 2);
    }

    /**
     * Get elapsed duration in hours.
     */
    public function durationInHours(): ?float
    {
        if ($this->duration_seconds === null) {
            return null;
        }

        return round($this->duration_seconds / 3600, 1);
    }

    /**
     * Get a human-readable duration label.
     */
    public function formattedDuration(): string
    {
        if ($this->duration_seconds === null || $this->duration_seconds <= 0) {
            return '< 1 min';
        }

        $days = (int) floor($this->duration_seconds / 86400);
        $hours = (int) floor(($this->duration_seconds % 86400) / 3600);
        $minutes = (int) floor(($this->duration_seconds % 3600) / 60);

        if ($days > 0) {
            return $days === 1 ? '1 day' : "{$days} days";
        }

        if ($hours > 0) {
            return $hours === 1 ? '1 hour' : "{$hours} hours";
        }

        return $minutes <= 1 ? '1 min' : "{$minutes} mins";
    }
}
