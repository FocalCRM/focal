<?php

declare(strict_types=1);

namespace Odden\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Odden\Core\Models\Contact;
use Odden\Core\Models\PropertyHistory;

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
