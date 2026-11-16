<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'created_by' => null,
            'type' => fake()->randomElement(['consultation', 'operation', 'checkup', 'emergency']),
            'scheduled_at' => fake()->dateTimeBetween('+1 day', '+1 month'),
            'duration_minutes' => 15,
            'queue_number' => fake()->numberBetween(1, 100),
            'status' => 'scheduled',
            'diagnosis' => fake()->optional(0.3)->sentence(),
            'notes' => fake()->optional(0.5)->sentence(),
        ];
    }
}
