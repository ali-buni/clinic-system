<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Work_hour;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkHourFactory extends Factory
{
    protected $model = Work_hour::class;

    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'start_time' => fake()->randomElement(['08:00', '09:00', '10:00']),
            'end_time' => fake()->randomElement(['16:00', '17:00', '18:00']),
            'is_active' => true,
            'max_patients_per_day' => fake()->numberBetween(10, 30),
            'break_start' => fake()->randomElement([null, '12:00', '13:00']),
            'break_end' => fake()->randomElement([null, '13:00', '14:00']),
        ];
    }
}
