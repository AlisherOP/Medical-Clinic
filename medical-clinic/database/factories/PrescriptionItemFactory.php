<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrescriptionItem>
 */
class PrescriptionItemFactory extends Factory
{
    protected $model = PrescriptionItem::class;

    public function definition(): array
    {
        return [
            'prescription_id' => Prescription::factory(),
            'medicine_id' => Medicine::factory(),
            'dosage' => fake()->randomElement(['250mg', '500mg', '10mg', '5ml']),
            'frequency' => fake()->randomElement(['Once daily', 'Twice daily', 'Three times daily']),
            'duration_days' => fake()->numberBetween(3, 14),
            'quantity' => fake()->numberBetween(6, 30),
            'instructions' => fake()->randomElement(['Take after meals', 'Take before bedtime', 'Take with plenty of water']),
            'dispensed_at' => null,
            'dispensed_by' => null,
        ];
    }
}
