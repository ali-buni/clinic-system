<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 days', '+30 days');
        $duration = fake()->randomElement([20, 30, 45, 60]);

        return [
            'clinic_id' => Clinic::factory(),
            'doctor_id' => Doctor::factory(),
            'patient_id' => Patient::factory(),
            'appointment_type_id' => null,
            'start_time' => $start,
            'end_time' => (clone $start)->modify("+{$duration} minutes"),
            'status' => fake()->randomElement(['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show']),
            'cancel_reason' => null,
            'visit_reason' => fake()->sentence(),
            'visit_in_time' => fake()->boolean(75),
            'requires_followup' => fake()->boolean(30),
            'notes' => fake()->paragraph(),
        ];
    }
}
