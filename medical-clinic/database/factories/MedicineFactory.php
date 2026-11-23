<?php

namespace Database\Factories;

use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medicine>
 */
class MedicineFactory extends Factory
{
    protected $model = Medicine::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'generic_name' => fake()->word(),
            'category' => fake()->randomElement(['Antibiotic', 'Analgesic', 'Antihistamine', 'Antipyretic', 'Cardiovascular']),
            'stock_quantity' => fake()->numberBetween(10, 500),
            'unit' => 'tablets',
            'unit_price' => fake()->randomElement([20.00, 50.00, 120.00, 250.00, 400.00]),
            'expiry_date' => fake()->dateTimeBetween('+6 months', '+3 years')->format('Y-m-d'),
        ];
    }
}
