<?php

namespace Database\Factories;

use App\Models\Appointment_type;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentTypeFactory extends Factory
{
    protected $model = Appointment_type::class;

    public function definition(): array
    {
        return [
            'ar_name' => fake()->unique()->word(),
            'en_name' => fake()->unique()->word(),
            'types' => fake()->randomElement([1, 2, 3]),
        ];
    }
}
