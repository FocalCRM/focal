<?php

declare(strict_types=1);

namespace Odden\Core\Traits;

use BackedEnum;
use Odden\Core\Models\PropertyHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait AuditsProperties
{
    /**
     * Boot the trait to track property updates.
     */
    public static function bootAuditsProperties(): void
    {
        static::updating(function (Model $model): void {
            $dirty = $model->getDirty();
            $original = $model->getOriginal();
            $userId = auth()->id();

            // Ignored attributes from property change history
            $ignored = ['updated_at', 'deleted_at', 'remember_token'];

            foreach ($dirty as $key => $newValue) {
                if (in_array($key, $ignored, true)) {
                    continue;
                }

                if ($key === 'properties') {
                    // Diff the dynamic properties array
                    /** @var array<string, mixed> $oldProperties */
                    $oldProperties = is_string($original['properties'] ?? null)
                        ? (json_decode((string) $original['properties'], true) ?? [])
                        : ($original['properties'] ?? []);

                    /** @var array<string, mixed> $newProperties */
                    $newProperties = is_string($newValue)
                        ? (json_decode((string) $newValue, true) ?? [])
                        : (is_array($newValue) ? $newValue : []);

                    $allPropertyKeys = array_unique(array_merge(array_keys($oldProperties), array_keys($newProperties)));

                    foreach ($allPropertyKeys as $propKey) {
                        $oldVal = $oldProperties[$propKey] ?? null;
                        $newVal = $newProperties[$propKey] ?? null;

                        if ($oldVal !== $newVal) {
                            PropertyHistory::create([
                                'auditable_type' => $model->getMorphClass(),
                                'auditable_id' => $model->getKey(),
                                'property_name' => (string) $propKey,
                                'old_value' => is_scalar($oldVal) ? (string) $oldVal : json_encode($oldVal),
                                'new_value' => is_scalar($newVal) ? (string) $newVal : json_encode($newVal),
                                'user_id' => $userId,
                                'source' => request()->header('X-Odden-Source', 'web'),
                                'created_at' => now(),
                            ]);
                        }
                    }

                    continue;
                }

                $oldVal = $original[$key] ?? null;

                PropertyHistory::create([
                    'auditable_type' => $model->getMorphClass(),
                    'auditable_id' => $model->getKey(),
                    'property_name' => $key,
                    'old_value' => is_scalar($oldVal) ? (string) $oldVal : ($oldVal instanceof BackedEnum ? (string) $oldVal->value : json_encode($oldVal)),
                    'new_value' => is_scalar($newValue) ? (string) $newValue : ($newValue instanceof BackedEnum ? (string) $newValue->value : json_encode($newValue)),
                    'user_id' => $userId,
                    'source' => request()->header('X-Odden-Source', 'web'),
                    'created_at' => now(),
                ]);
            }
        });
    }

    /**
     * History of property changes on this record.
     *
     * @return MorphMany<PropertyHistory, $this>
     */
    public function propertyHistory(): MorphMany
    {
        return $this->morphMany(PropertyHistory::class, 'auditable')->latest('created_at');
    }
}
