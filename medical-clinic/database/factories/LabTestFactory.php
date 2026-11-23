<?php

namespace Database\Factories;

use App\Models\LabTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LabTest>
 */
class LabTestFactory extends Factory
{
    protected $model = LabTest::class;

    public function definition(): array
    {
        return [
            'test_name' => fake()->unique()->words(2, true),
            'code' => 'LT-'.fake()->unique()->numberBetween(100, 999),
            'category' => fake()->randomElement(['Hematology', 'Biochemistry', 'Microbiology', 'Immunology']),
            'price' => fake()->randomElement([300.00, 500.00, 750.00, 1200.00]),
            'turnaround_time_hours' => 24,
        ];
    }
}
