<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\PatientInfo;
use App\Models\Patient_record;
use Illuminate\Database\Eloquent\Factories\Factory;

class Patient_recordFactory extends Factory
{
    protected $model = Patient_record::class;

    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'patient_id' => PatientInfo::factory(),
            'doctor_id' => Doctor::factory(),
            'appointment_id' => Appointment::factory(),
            'diagnosis_summary' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['open', 'closed', 'follow-up']),
            'notes' => fake()->paragraph(),
        ];
    }
}
