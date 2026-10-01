<?php

declare(strict_types=1);

namespace Focal\Core\Actions;

use Carbon\CarbonInterface;
use Focal\Core\Enums\ActivityStatus;
use Focal\Core\Enums\ActivityType;
use Focal\Core\Events\ActivityLogged;
use Focal\Core\Models\Activity;
use Illuminate\Database\Eloquent\Model;

class LogActivityAction
{
    /**
     * Log an activity on any model.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function execute(
        Model $subject,
        ActivityType|string $type,
        string $title,
        ?string $body = null,
        array $metadata = [],
        ActivityStatus|string $status = ActivityStatus::Completed,
        ?CarbonInterface $dueAt = null,
        ?int $creatorId = null
    ): Activity {
        $activity = Activity::create([
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'type' => $type instanceof ActivityType ? $type->value : $type,
            'status' => $status instanceof ActivityStatus ? $status->value : $status,
            'title' => $title,
            'body' => $body,
            'metadata' => $metadata,
            'due_at' => $dueAt,
            'completed_at' => ($status === ActivityStatus::Completed || $status === ActivityStatus::Completed->value) ? now() : null,
            'creator_id' => $creatorId,
        ]);

        event(new ActivityLogged($activity));

        return $activity;
    }
}
