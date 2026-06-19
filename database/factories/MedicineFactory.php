<?php

namespace Database\Factories;

use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineFactory extends Factory
{
    protected $model = Medicine::class;

    public function definition(): array
    {
        return [
            'ar_name' => fake()->unique()->word(),
            'en_name' => fake()->unique()->word(),
            'generic_name_en' => fake()->word(),
            'generic_name_ar' => null,
            'strength' => fake()->randomElement(['500mg', '250mg', '10mg', '5mg', '1000mg']),
            'form' => fake()->randomElement(['tablet', 'capsule', 'syrup', 'injection', 'ointment']),
            'api_medicine_id' => null,
            'is_custom' => true,
        ];
    }
}
