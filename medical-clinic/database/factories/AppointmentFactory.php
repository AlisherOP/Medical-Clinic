<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => \App\Models\Patient::factory(),
            'doctor_id' => \App\Models\Doctor::factory(),
            'type' => 'consultation',
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            'queue_number' => $this->faker->numberBetween(1, 50),
            'status' => 'scheduled',
        ];
    }
}