<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\PatientInfo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientInfoFactory extends Factory
{
    protected $model = PatientInfo::class;

    public function definition(): array
    {
        $user = User::factory()->create();
        $user->assignRole('patient');

        return [
            'user_id' => $user->id,
            'clinic_id' => Clinic::factory(),
            'nationality' => fake()->country(),
            'address' => fake()->address(),
            'marital_status' => fake()->randomElement(['married', 'single', 'other']),
            'emergency_phone' => fake()->numerify('09########'),
            'allergies' => fake()->sentence(),
            'chronic_conditions' => fake()->sentence(),
            'career' => fake()->jobTitle(),
            'blood_type' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
        ];
    }
}
