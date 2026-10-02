<?php

declare(strict_types=1);

namespace Odden\Core\Database\Factories;

use Odden\Core\Enums\PropertyType;
use Odden\Core\Models\Contact;
use Odden\Core\Models\PropertyDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyDefinition>
 */
class PropertyDefinitionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<PropertyDefinition>
     */
    protected $model = PropertyDefinition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->slug(2);

        return [
            'entity_type' => (new Contact)->getMorphClass(),
            'name' => str_replace('-', '_', $name),
            'label' => ucwords(str_replace('-', ' ', $name)),
            'type' => fake()->randomElement(PropertyType::cases()),
            'group_name' => 'General Information',
            'options' => null,
            'description' => fake()->sentence(),
            'is_required' => false,
            'is_searchable' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
