<?php

namespace Database\Factories;

use App\Models\Disease;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiseaseFactory extends Factory
{
    protected $model = Disease::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('???###')),
            'ar_name' => fake()->unique()->word(),
            'en_name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
            'disease_nature' => fake()->randomElement(['infectious', 'genetic', 'chronic', 'acute', 'mental', 'other']),
            'is_custom' => true,
        ];
    }
}
