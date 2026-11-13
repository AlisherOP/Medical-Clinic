<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'General Medicine', 'Cardiology', 'Pediatrics', 'Neurology',
                'Orthopedics', 'Dermatology', 'Gynecology', 'Ophthalmology',
                'ENT', 'Surgery', 'Radiology', 'Emergency',
            ]),
        ];
    }
}
