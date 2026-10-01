<?php

declare(strict_types=1);

namespace Focal\Core\Database\Factories;

use Focal\Core\Enums\ActivityStatus;
use Focal\Core\Enums\ActivityType;
use Focal\Core\Models\Activity;
use Focal\Core\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Activity>
     */
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_type' => (new Contact)->getMorphClass(),
            'subject_id' => 1,
            'type' => fake()->randomElement(ActivityType::cases()),
            'status' => ActivityStatus::Completed,
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'due_at' => null,
            'completed_at' => now(),
            'creator_id' => null,
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the activity is a pending task.
     */
    public function pendingTask(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ActivityType::Task,
            'status' => ActivityStatus::Pending,
            'due_at' => now()->addDays(2),
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the activity is a note.
     */
    public function note(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ActivityType::Note,
            'status' => ActivityStatus::Completed,
            'due_at' => null,
            'completed_at' => now(),
        ]);
    }
}
