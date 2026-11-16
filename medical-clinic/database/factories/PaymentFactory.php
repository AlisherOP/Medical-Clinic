<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'received_by' => User::factory()->role('cashier'),
            'type' => 'payment',
            'amount' => 1000.00,
            'payment_method' => fake()->randomElement(['cash', 'credit_card', 'insurance', 'bank_transfer']),
            'transaction_reference' => 'TXN-'.fake()->unique()->numerify('########'),
        ];
    }
}
