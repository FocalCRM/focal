<?php

declare(strict_types=1);

namespace Odden\Core\Database\Factories;

use Odden\Core\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Company>
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'domain' => fake()->unique()->domainName(),
            'phone' => fake()->phoneNumber(),
            'industry' => fake()->randomElement(['Technology', 'Healthcare', 'Finance', 'Manufacturing', 'Retail']),
            'properties' => [
                'employees_count' => fake()->numberBetween(5, 500),
                'city' => fake()->city(),
            ],
            'owner_id' => null,
        ];
    }
}
