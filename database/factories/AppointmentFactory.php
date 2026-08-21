<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Appointment_type;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\PatientInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 days', '+30 days');
        $duration = fake()->randomElement([20, 30, 45, 60]);
        $status = fake()->randomElement(['scheduled', 'completed', 'cancelled', 'no_show']);

        return [
            'clinic_id' => Clinic::factory(),
            'doctor_id' => Doctor::factory(),
            'patient_id' => PatientInfo::factory(),
            'appointment_type_id' => Appointment_type::factory(),
            'start_time' => $start,
            'end_time' => (clone $start)->modify("+{$duration} minutes"),
            'status' => $status,
            'cancel_reason' => in_array($status, ['cancelled', 'no_show']) ? fake()->sentence() : null,
            'visit_reason' => fake()->sentence(),
            'visit_in_time' => $status === 'completed' ? fake()->boolean(75) : null,
        ];
    }
}
