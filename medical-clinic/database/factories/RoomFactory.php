<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'room_number' => 'R-'.fake()->unique()->numberBetween(100, 999),
            'type' => fake()->randomElement(['general_ward', 'icu', 'operation_theater', 'private']),
            'daily_rate' => fake()->randomElement([500.00, 1000.00, 2000.00, 3500.00]),
            'is_active' => true,
        ];
    }
}
