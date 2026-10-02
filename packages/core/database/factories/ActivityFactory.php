<?php

declare(strict_types=1);

namespace Odden\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Odden\Core\Enums\ActivityStatus;
use Odden\Core\Enums\ActivityType;
use Odden\Core\Models\Activity;
use Odden\Core\Models\Contact;

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
