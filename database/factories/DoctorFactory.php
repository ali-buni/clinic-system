<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'clinic_id' => Clinic::factory(),
            'room_id' => Room::factory(),
            'bio' => fake()->paragraph(),
            'appointment_duration' => fake()->randomElement([20, 30, 40, 45]),
            'consultation_fee' => fake()->randomFloat(2, 120, 300),
        ];
    }
}
