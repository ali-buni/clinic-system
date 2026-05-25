<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'patient_id' => Patient::factory(),
            'appointment_id' => Appointment::factory(),
            'invoice_number' => strtoupper('INV-' . fake()->unique()->bothify('???###')),
            'status' => fake()->randomElement(['draft', 'issued', 'partially_paid', 'paid', 'void', 'refunded']),
            'total_cost' => fake()->randomFloat(2, 80, 1200),
            'description' => fake()->sentence(),
        ];
    }
}
