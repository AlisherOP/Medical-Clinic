<?php

namespace Database\Factories;

use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BedAssignment>
 */
class BedAssignmentFactory extends Factory
{
    protected $model = BedAssignment::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'bed_id' => Bed::factory(),
            'appointment_id' => null,
            'admitted_at' => now(),
            'discharged_at' => null,
            'status' => 'admitted',
        ];
    }

    public function discharged(): static
    {
        return $this->state(fn () => [
            'status' => 'discharged',
            'admitted_at' => now()->subDays(2),
            'discharged_at' => now()->subDay(),
        ]);
    }
}
