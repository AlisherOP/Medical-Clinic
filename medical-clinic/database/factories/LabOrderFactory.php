<?php

namespace Database\Factories;

use App\Models\LabOrder;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LabOrder>
 */
class LabOrderFactory extends Factory
{
    protected $model = LabOrder::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'appointment_id' => null,
            'lab_test_id' => LabTest::factory(),
            'performed_by' => null,
            'status' => 'requested',
            'result_summary' => null,
            'attachment_url' => null,
            'ordered_at' => now(),
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'performed_by' => User::factory()->role('lab_technician'),
            'result_summary' => fake()->paragraph(),
            'completed_at' => now(),
        ]);
    }
}
