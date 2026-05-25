<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Room;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SecretaryFactory extends Factory
{
    protected $model = Secretary::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'clinic_id' => Clinic::factory(),
            'room_id' => Room::factory(),
        ];
    }
}
