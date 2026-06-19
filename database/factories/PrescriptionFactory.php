<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient_record;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        return [
            'patient_record_id' => Patient_record::factory(),
            'doctor_id' => Doctor::factory(),
            'valid_until' => fake()->dateTimeBetween('+1 month', '+6 months'),
            'issued_at' => now(),
            'cost' => fake()->randomFloat(2, 20, 500),
            'notes' => fake()->sentence(),
        ];
    }
}
