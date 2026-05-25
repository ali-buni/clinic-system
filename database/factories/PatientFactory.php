<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'fname' => fake()->firstName(),
            'lname' => fake()->lastName(),
            'dob' => fake()->dateTimeBetween('-65 years', '-18 years'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'phone' => fake()->unique()->numerify('09########'),
            'nationality' => fake()->country(),
            'address' => fake()->address(),
            'marital_status' => fake()->randomElement(['married', 'single', 'other']),
            'emergency_phone' => fake()->numerify('09########'),
            'allergies' => fake()->boolean(30) ? fake()->sentence() : null,
            'chronic_conditions' => fake()->boolean(25) ? fake()->sentence() : null,
            'career' => fake()->jobTitle(),
            'blood_type' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
        ];
    }
}
