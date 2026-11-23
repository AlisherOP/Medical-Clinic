<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\PatientVital;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientVital>
 */
class PatientVitalFactory extends Factory
{
    protected $model = PatientVital::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'bed_assignment_id' => null,
            'recorded_by' => User::factory()->role('nurse'),
            'systolic_bp' => fake()->numberBetween(110, 140),
            'diastolic_bp' => fake()->numberBetween(70, 90),
            'heart_rate' => fake()->numberBetween(60, 100),
            'temperature' => fake()->randomFloat(1, 36.1, 38.5),
            'weight_kg' => fake()->randomFloat(2, 50.00, 100.00),
            'height_cm' => fake()->randomFloat(2, 150.00, 195.00),
        ];
    }
}
