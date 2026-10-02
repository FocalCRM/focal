<?php

declare(strict_types=1);

namespace Odden\Core\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Odden\Core\Database\Factories\ActivityFactory;
use Odden\Core\Enums\ActivityStatus;
use Odden\Core\Enums\ActivityType;
use Odden\Core\Support\UserModel;

/**
 * @property int $id
 * @property string $subject_type
 * @property int $subject_id
 * @property ActivityType $type
 * @property ActivityStatus $status
 * @property string $title
 * @property string|null $body
 * @property CarbonInterface|null $due_at
 * @property CarbonInterface|null $completed_at
 * @property int|null $creator_id
 * @property array<string, mixed>|null $metadata
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'subject_type',
        'subject_id',
        'type',
        'status',
        'title',
        'body',
        'due_at',
        'completed_at',
        'creator_id',
        'metadata',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('odden-core.tables.activities', 'odden_activities');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'status' => ActivityStatus::class,
            'metadata' => 'array',
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the subject record that owns the activity.
     *
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this activity.
     *
     * @return BelongsTo<Model, $this>
     */
    public function creator(): BelongsTo
    {
        $userModel = UserModel::className();

        return $this->belongsTo($userModel, 'creator_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ActivityFactory
    {
        return ActivityFactory::new();
    }
}
