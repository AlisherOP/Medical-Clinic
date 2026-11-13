<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Doctor>
 */
class DoctorFactory extends Factory
{
    private const SPECIALIZATIONS = [
        'General Medicine' => 'General Practitioner',
        'Cardiology' => 'Cardiologist',
        'Pediatrics' => 'Pediatrician',
        'Neurology' => 'Neurologist',
        'Orthopedics' => 'Orthopedic Surgeon',
        'Dermatology' => 'Dermatologist',
        'Gynecology' => 'Gynecologist',
        'Ophthalmology' => 'Ophthalmologist',
        'ENT' => 'Otolaryngologist',
        'Surgery' => 'General Surgeon',
        'Radiology' => 'Radiologist',
        'Emergency' => 'Emergency Physician',
    ];

    public function definition(): array
    {
        return [
            // Always paired with a user whose role matches the profile
            'user_id' => User::factory()->role('doctor'),
            'department_id' => Department::factory(),
            // Keep specialization consistent with the department it belongs to
            'specialization' => fn (array $attributes) => self::SPECIALIZATIONS[
                Department::find($attributes['department_id'])?->name
            ] ?? 'General Practitioner',
            'license_number' => 'LIC-'.fake()->unique()->numerify('######'),
            'consultation_fee' => fake()->randomElement([500, 800, 1000, 1500, 2000]),
        ];
    }
}
