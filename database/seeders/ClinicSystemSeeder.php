<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Appointment_type;
use App\Models\Clinic;
use App\Models\Disease;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\PatientInfo;
use App\Models\Patient_record;
use App\Models\Payment;
use App\Models\Payment_method;
use App\Models\Prescription;
use App\Models\Prescription_item;
use App\Models\Room;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClinicSystemSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethods = collect([
            ['ar_name' => 'نقداً', 'en_name' => 'Cash', 'is_active' => true],
            ['ar_name' => 'بطاقة', 'en_name' => 'Card', 'is_active' => true],
            ['ar_name' => 'تحويل مصرفي', 'en_name' => 'Bank Transfer', 'is_active' => true],
        ])->map(fn(array $data) => Payment_method::firstOrCreate($data));

        $owner = User::factory()->create([
            'phone' => '0951232317',
            'email' => 'aliboune184@gmail.com',
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

        $rooms = collect(range(1, 3))->map(function () use ($clinic) {
            return Room::factory()->create([
                'clinic_id' => $clinic->id,
                'name' => fake()->unique()->word(),
            ]);
        });

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
        ]);

        $patients = PatientInfo::factory()->count(20)->for($clinic)->create();

        $diseases = Disease::all();
        $medicines = Medicine::all();

        $appointments = collect();
        $usedSlots = [];

        foreach ($patients as $patient) {
            $doctor = $doctors->random();
            $types = Appointment_type::query()->get();
            $type = $types->random();

            $start = null;
            $attempts = 0;
            $maxAttempts = 100;

            do {
                $start = fake()->dateTimeBetween('-20 days', 'now')
                    ->setTime(fake()->numberBetween(8, 15), 0);
                $key = $clinic->id . '_' . $doctor->id . '_' . $start->format('Y-m-d H:i:s');
                $attempts++;
            } while (isset($usedSlots[$key]) && $attempts < $maxAttempts);

            if ($attempts >= $maxAttempts) {
                continue;
            }

            $usedSlots[$key] = true;
            $end = (clone $start)->modify('+' . $doctor->appointment_duration . ' minutes');

            $appointments->push(
                Appointment::factory()->create([
                    'clinic_id' => $clinic->id,
                    'doctor_id' => $doctor->id,
                    'patient_id' => $patient->id,
                    'appointment_type_id' => $type->id,
                    'start_time' => $start,
                    'end_time' => $end,
                    'status' => fake()->randomElement(['scheduled', 'completed', 'cancelled', 'no_show']),
                    'visit_reason' => fake()->sentence(),
                    'visit_in_time' => fake()->boolean(80),
                ])
            );
        }

        $appointments->each(function (Appointment $appointment) use ($clinic, $diseases, $medicines, $doctors) {
            $record = Patient_record::factory()->create([
                'clinic_id' => $clinic->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'diagnosis_summary' => fake()->sentence(),
                'description' => fake()->paragraph(),
                'status' => fake()->randomElement(['open', 'closed', 'follow-up']),
                'notes' => fake()->paragraph(),
            ]);

            $assignedDiseases = $diseases->random(fake()->numberBetween(1, 3));
            foreach ($assignedDiseases as $disease) {
                $record->diseases()->attach($disease->id, [
                    'status'   => fake()->randomElement(['active', 'resolved', 'chronic']),
                    'severity' => fake()->randomElement(['mild', 'moderate', 'severe']),
                ]);
            }

            if (fake()->boolean(70)) {
                $doctor = $doctors->random();
                $prescription = Prescription::create([
                    'patient_record_id' => $record->id,
                    'doctor_id'         => $appointment->doctor_id,
                    'valid_until'       => now()->addDays(fake()->numberBetween(7, 90)),
                    'issued_at'         => now(),
                    'cost'              => fake()->randomFloat(2, 10, 150),
                ]);

                $prescriptionMedicines = $medicines->random(fake()->numberBetween(1, 4));
                foreach ($prescriptionMedicines as $medicine) {
                    Prescription_item::create([
                        'prescription_id'    => $prescription->id,
                        'medicine_id'        => $medicine->id,
                        'dosage_instruction' => fake()->randomElement(['1 tablet daily', '1 tablet twice daily', '1 tablet 3 times daily', '2 tablets daily', '1 capsule daily']),
                        'frequency'          => fake()->randomElement(['once daily', 'twice daily', 'three times daily', 'every 8 hours', 'every 12 hours']),
                        'duration'           => fake()->randomElement(['5 days', '7 days', '10 days', '14 days', '30 days']),
                    ]);
                }
            }
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
