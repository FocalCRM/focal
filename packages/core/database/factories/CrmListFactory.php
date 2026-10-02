<?php

declare(strict_types=1);

namespace Odden\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Odden\Core\Enums\ListType;
use Odden\Core\Models\Contact;
use Odden\Core\Models\CrmList;

/**
 * @extends Factory<CrmList>
 */
class CrmListFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<CrmList>
     */
    protected $model = CrmList::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'entity_type' => (new Contact)->getMorphClass(),
            'type' => ListType::Static,
            'criteria' => null,
            'created_by_id' => null,
        ];
    }

    /**
     * Indicate that the list is an active/dynamic smart list.
     *
     * @param  array<array<string, mixed>>|null  $criteria
     */
    public function active(?array $criteria = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ListType::Active,
            'criteria' => $criteria ?? [
                [
                    'field' => 'lifecycle_stage',
                    'operator' => 'equals',
                    'value' => 'lead',
                ],
            ],
        ]);
    }
}
