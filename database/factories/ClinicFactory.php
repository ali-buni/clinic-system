<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClinicFactory extends Factory
{
    protected $model = Clinic::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'location' => fake()->address(),
            'title' => fake()->company() . ' Clinic',
            'phone' => fake()->unique()->numerify('09########'),
        ];
    }
}
