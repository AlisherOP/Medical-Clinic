<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_number' => $this->faker->unique()->bothify('R-###'),
            'type' => 1,
            'daily_rate' => $this->faker->randomFloat(2, 50, 500),
            'is_active' => true,
        ];
    }
}