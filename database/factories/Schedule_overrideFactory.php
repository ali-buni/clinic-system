<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Schedule_override;
use Illuminate\Database\Eloquent\Factories\Factory;

class Schedule_overrideFactory extends Factory
{
    protected $model = Schedule_override::class;

    public function definition(): array
    {
        return [
            'doctor_id'     => Doctor::factory(),
            'override_date' => fake()->dateTimeBetween('+1 week', '+1 month')->format('Y-m-d'),
            'override_type' => fake()->randomElement(['time_change', 'closed', 'extended']),
            'start_time'    => fake()->randomElement([null, '08:00', '09:00', '10:00']),
            'end_time'      => fake()->randomElement([null, '14:00', '15:00', '16:00']),
            'reason'        => fake()->optional()->sentence(),
            'is_closed'     => fake()->boolean(30),
        ];
    }
}
