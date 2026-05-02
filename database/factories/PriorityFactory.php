<?php

namespace Database\Factories;

use App\Models\Priority;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Priority>
 */
class PriorityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Low', 'Medium', 'High', 'Critical']),
            'level' => $this->faker->numberBetween(1, 4),
            'response_time' => $this->faker->numberBetween(1, 24),
            'resolution_time' => $this->faker->numberBetween(8, 120),
        ];
    }
}
