<?php

declare(strict_types=1);

namespace Focal\Core\Database\Factories;

use Focal\Core\Models\Contact;
use Focal\Core\Models\PropertyHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyHistory>
 */
class PropertyHistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<PropertyHistory>
     */
    protected $model = PropertyHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'auditable_type' => (new Contact)->getMorphClass(),
            'auditable_id' => 1,
            'property_name' => 'lifecycle_stage',
            'old_value' => 'lead',
            'new_value' => 'customer',
            'user_id' => null,
            'source' => 'system',
            'created_at' => now(),
        ];
    }
}
