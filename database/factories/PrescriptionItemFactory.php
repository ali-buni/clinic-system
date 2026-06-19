<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\Prescription_item;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionItemFactory extends Factory
{
    protected $model = Prescription_item::class;

    public function definition(): array
    {
        return [
            'prescription_id' => Prescription::factory(),
            'medicine_id' => Medicine::factory(),
            'dosage_instruction' => fake()->randomElement(['Take one daily', 'Take twice daily', 'Take with food', 'Take before bed']),
            'frequency' => fake()->randomElement(['once daily', 'twice daily', 'three times daily', 'as needed']),
            'duration' => fake()->randomElement(['7 days', '10 days', '14 days', '1 month', '3 months']),
        ];
    }
}
