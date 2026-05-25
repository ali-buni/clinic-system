<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'name' => fake()->randomElement(['Room A', 'Room B', 'Room C', 'Room D']),
        ];
    }
}
