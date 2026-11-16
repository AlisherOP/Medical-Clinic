<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'appointment_id' => null,
            'total_amount' => 1000.00,
            'discount_amount' => 0.00,
            'paid_amount' => 0.00,
            'status' => 'unpaid',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'paid_amount' => 1000.00,
            'status' => 'paid',
        ]);
    }
}
