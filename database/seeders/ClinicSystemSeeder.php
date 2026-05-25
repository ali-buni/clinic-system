<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Appointment_type;
use App\Models\AppointmentType;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Patient_record;
use App\Models\Payment;
use App\Models\Payment_method;
use App\Models\Room;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClinicSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appointmentTypes = collect([
            ['ar_name' => 'استشارة عامة', 'en_name' => 'General Consultation'],
            ['ar_name' => 'متابعة', 'en_name' => 'Follow Up'],
            ['ar_name' => 'طوارئ', 'en_name' => 'Emergency'],
        ])->map(fn(array $data) => Appointment_type::firstOrCreate($data));

        $paymentMethods = collect([
            ['ar_name' => 'نقداً', 'en_name' => 'Cash', 'is_active' => true],
            ['ar_name' => 'بطاقة', 'en_name' => 'Card', 'is_active' => true],
            ['ar_name' => 'تحويل مصرفي', 'en_name' => 'Bank Transfer', 'is_active' => true],
        ])->map(fn(array $data) => Payment_method::firstOrCreate($data));


        // clinic owner /////////////////////////////////////////////
        $owner = User::factory()->create([
            'phone' => '0951232317',
            'fname' => 'Clinic',
            'lname' => 'Owner',
            'gender' => 'male',
        ]);
        $owner->assignRole('owner');

        $clinic = Clinic::factory()->create([
            'user_id' => $owner->id,
            'location' => '123 Main Street',
            'title' => 'Central Clinic',
            'phone' => '0951232317',
        ]);

        $rooms = Room::factory()->count(3)->for($clinic)->create([
            'name' => fake()->word(),
        ]);

        $doctors = collect([
            ['fname' => 'Amira', 'lname' => 'Hassan', 'gender' => 'female'],
            ['fname' => 'Omar', 'lname' => 'Nasser', 'gender' => 'male'],
            ['fname' => 'Layla', 'lname' => 'Farah', 'gender' => 'female'],
        ])->map(function (array $userData, int $index) use ($clinic, $rooms) {
            $user = User::factory()->create([
                'phone' => '095123230' . ($index + 1),
                'fname' => $userData['fname'],
                'lname' => $userData['lname'],
                'gender' => $userData['gender'],
            ]);
            $user->assignRole('doctor');

            return Doctor::factory()->create([
                'user_id' => $user->id,
                'clinic_id' => $clinic->id,
                'room_id' => $rooms->get($index % $rooms->count())->id,
                'appointment_duration' => 30,
                'consultation_fee' => fake()->randomFloat(2, 120, 300),
            ]);
        });

        $secretaryUser = User::factory()->create([
            'phone' => '0900000000',
            'fname' => 'Sara',
            'lname' => 'Ali',
            'gender' => 'female',
        ]);
        $secretaryUser->assignRole('secretary');

        Secretary::factory()->create([
            'user_id' => $secretaryUser->id,
            'clinic_id' => $clinic->id,
            'room_id' => $rooms->first()->id,
        ]);

        $patients = Patient::factory()->count(12)->for($clinic)->create();

        $appointments = collect();
        foreach ($patients as $patient) {
            $doctor = $doctors->random();
            $type = $appointmentTypes->random();

            // Generate a random appointment time within the last 20 days, between 8 AM and 3 PM
            $start = fake()->dateTimeBetween('-20 days', 'now')->setTime(fake()->numberBetween(8, 15), 0);
            $end = (clone $start)->modify('+' . $doctor->appointment_duration . ' minutes');

            $appointments->push(
                Appointment::factory()->create([
                    'clinic_id' => $clinic->id,
                    'doctor_id' => $doctor->id,
                    'patient_id' => $patient->id,
                    'appointment_type_id' => $type->id,
                    'start_time' => $start,
                    'end_time' => $end,
                    'status' => fake()->randomElement(['scheduled', 'confirmed', 'completed']),
                    'visit_reason' => fake()->sentence(),
                    'visit_in_time' => fake()->boolean(80),
                    'requires_followup' => fake()->boolean(25),
                    'notes' => fake()->paragraph(),
                ])
            );
        }

        $appointments->each(function (Appointment $appointment) use ($clinic) {
            Patient_record::factory()->create([
                'clinic_id' => $clinic->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'diagnosis_summary' => fake()->sentence(),
                'description' => fake()->paragraph(),
                'status' => fake()->randomElement(['open', 'closed', 'follow-up']),
                'notes' => fake()->paragraph(),
            ]);
        });

        $appointments->each(function (Appointment $appointment) use ($clinic, $paymentMethods) {
            $invoice = Invoice::factory()->create([
                'clinic_id' => $clinic->id,
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'status' => fake()->randomElement(['issued', 'paid', 'partially_paid', 'draft']),
                'total_cost' => fake()->randomFloat(2, 150, 900),
                'description' => fake()->sentence(),
            ]);

            if (in_array($invoice->status, ['paid', 'partially_paid'], true)) {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'payment_method_id' => $paymentMethods->random()->id,
                    'amount' => $invoice->status == 'paid' ? $invoice->total_cost : $invoice->total_cost / 2,
                    'paid_at' => now(),
                ]);
            }
        });
    }
}
