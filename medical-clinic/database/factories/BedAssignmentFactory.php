<?php

namespace Database\Factories;

use App\Models\BedAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BedAssignment>
 */
class BedAssignmentFactory extends Factory
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
            'bed_id' => \App\Models\Bed::factory(),
            'appointment_id' => \App\Models\Appointment::factory(),
            'admitted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'discharged_at' => null, // Keeps it active so your dashboard query counts it!
            'status' => 'admitted',
        ];
    }
}
