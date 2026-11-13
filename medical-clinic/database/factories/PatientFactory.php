<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Always paired with a user whose role matches the profile
            'user_id' => User::factory()->role('patient'),
            'mrn' => 'MRN-'.fake()->unique()->numerify('######'),
            'blood_group' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'allergies' => fake()->optional(0.3)->randomElement(['Penicillin', 'Peanuts', 'Latex', 'Aspirin', 'Pollen']),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->numerify('+996 ### ### ###'),
            'insurance_provider' => fake()->optional(0.6)->company(),
            'insurance_policy_number' => fake()->optional(0.6)->bothify('POL-########'),
        ];
    }
}
