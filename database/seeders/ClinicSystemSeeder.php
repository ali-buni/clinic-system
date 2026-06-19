<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Appointment_type;
use App\Models\Clinic;
use App\Models\Disease;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Patient_record;
use App\Models\Payment;
use App\Models\Payment_method;
use App\Models\Room;
use App\Models\Schedule_override;
use App\Models\Secretary;
use App\Models\Specialty;
use App\Models\User;
use App\Models\Work_hour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClinicSystemSeeder extends Seeder
{
    public function run(): void
    {
        // Lookup data -----------------------------------------------------------
        $paymentMethods = collect([
            ['ar_name' => 'نقداً', 'en_name' => 'Cash', 'is_active' => true],
            ['ar_name' => 'بطاقة', 'en_name' => 'Card', 'is_active' => true],
            ['ar_name' => 'تحويل مصرفي', 'en_name' => 'Bank Transfer', 'is_active' => true],
        ])->map(fn(array $data) => Payment_method::firstOrCreate($data));

        $diseases = collect([
            ['code' => 'I10', 'en_name' => 'Hypertension', 'ar_name' => 'ارتفاع ضغط الدم', 'disease_nature' => 'chronic'],
            ['code' => 'E11', 'en_name' => 'Type 2 Diabetes', 'ar_name' => 'السكري من النوع الثاني', 'disease_nature' => 'chronic'],
            ['code' => 'J45', 'en_name' => 'Asthma', 'ar_name' => 'الربو', 'disease_nature' => 'chronic'],
            ['code' => 'M54.5', 'en_name' => 'Lower Back Pain', 'ar_name' => 'آلام أسفل الظهر', 'disease_nature' => 'acute'],
            ['code' => 'J06.9', 'en_name' => 'Acute Upper Respiratory Infection', 'ar_name' => 'التهاب الجهاز التنفسي العلوي الحاد', 'disease_nature' => 'acute'],
            ['code' => 'N39.0', 'en_name' => 'Urinary Tract Infection', 'ar_name' => 'التهاب المسالك البولية', 'disease_nature' => 'acute'],
            ['code' => 'F41.9', 'en_name' => 'Anxiety Disorder', 'ar_name' => 'اضطراب القلق', 'disease_nature' => 'mental'],
            ['code' => 'E03.9', 'en_name' => 'Hypothyroidism', 'ar_name' => 'قصور الغدة الدرقية', 'disease_nature' => 'chronic'],
        ])->map(fn(array $data) => Disease::firstOrCreate(
            ['code' => $data['code']],
            $data + ['description' => null, 'is_custom' => false]
        ));

        $medicines = collect([
            ['en_name' => 'Paracetamol', 'ar_name' => 'باراسيتامول', 'strength' => '500mg', 'form' => 'tablet'],
            ['en_name' => 'Amoxicillin', 'ar_name' => 'أموكسيسيلين', 'strength' => '500mg', 'form' => 'capsule'],
            ['en_name' => 'Metformin', 'ar_name' => 'ميتفورمين', 'strength' => '500mg', 'form' => 'tablet'],
            ['en_name' => 'Omeprazole', 'ar_name' => 'أوميبرازول', 'strength' => '20mg', 'form' => 'capsule'],
            ['en_name' => 'Ibuprofen', 'ar_name' => 'إيبوبروفين', 'strength' => '400mg', 'form' => 'tablet'],
            ['en_name' => 'Salbutamol Inhaler', 'ar_name' => 'سالبوتامول', 'strength' => '100mcg', 'form' => 'inhaler'],
            ['en_name' => 'Atorvastatin', 'ar_name' => 'أتورفاستاتين', 'strength' => '10mg', 'form' => 'tablet'],
        ])->map(fn(array $data) => Medicine::firstOrCreate(
            ['en_name' => $data['en_name']],
            $data + ['generic_name_en' => null, 'generic_name_ar' => null, 'api_medicine_id' => null, 'is_custom' => true]
        ));

        // Clinic owner ---------------------------------------------------------
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

        $roomNames = ['Consultation Room A', 'Consultation Room B', 'Procedure Room', 'Pediatrics Room', 'Emergency Room'];
        $rooms = collect(range(0, 4))->map(function (int $i) use ($clinic, $roomNames) {
            return Room::factory()->create([
                'clinic_id' => $clinic->id,
                'name' => $roomNames[$i] ?? 'Room ' . ($i + 1),
            ]);
        });

        // Doctors with specialties and work hours ------------------------------
        $doctorData = [
            ['fname' => 'Amira', 'lname' => 'Hassan', 'gender' => 'female', 'specialties' => ['Internal Medicine', 'Cardiology'], 'duration' => 30],
            ['fname' => 'Omar', 'lname' => 'Nasser', 'gender' => 'male', 'specialties' => ['Pediatrics'], 'duration' => 20],
            ['fname' => 'Layla', 'lname' => 'Farah', 'gender' => 'female', 'specialties' => ['Dermatology', 'General Medicine'], 'duration' => 45],
        ];

        $doctors = collect($doctorData)->map(function (array $data, int $index) use ($clinic, $rooms, $diseases) {
            $user = User::factory()->create([
                'phone' => '095123230' . ($index + 1),
                'fname' => $data['fname'],
                'lname' => $data['lname'],
                'gender' => $data['gender'],
            ]);
            $user->assignRole('doctor');

            $doctor = Doctor::factory()->create([
                'user_id' => $user->id,
                'clinic_id' => $clinic->id,
                'room_id' => $rooms->get($index % $rooms->count())->id,
                'appointment_duration' => $data['duration'],
                'consultation_fee' => [150, 200, 250][$index],
            ]);

            // Attach specialties
            $specialtyIds = Specialty::whereIn('en_name', $data['specialties'])->pluck('id');
            $syncData = [];
            foreach ($specialtyIds as $i => $sid) {
                $syncData[$sid] = ['is_primary' => $i === 0];
            }
            $doctor->specialties()->sync($syncData);

            // Work hours (Sunday-Thursday)
            $dayNames = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu'];
            foreach ($dayNames as $day => $name) {
                Work_hour::create([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $day,
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'is_active' => true,
                    'max_patients_per_day' => 20,
                    'break_start' => '13:00',
                    'break_end' => '14:00',
                ]);
            }

            return $doctor;
        });

        // Secretary ------------------------------------------------------------
        $secretaryUser = User::factory()->create([
            'phone' => '0900000000',
            'fname' => 'Sara',
            'lname' => 'Ali',
            'gender' => 'female',
        ]);
        $secretaryUser->assignRole('secretary');

        $secretary = Secretary::factory()->create([
            'user_id' => $secretaryUser->id,
            'clinic_id' => $clinic->id,
        ]);
        $secretary->rooms()->sync($rooms->pluck('id'));

        // Schedule overrides for variety ---------------------------------------
        $doctors->each(function (Doctor $doctor) {
            if (fake()->boolean(30)) {
                $futureDate = now()->addDays(fake()->numberBetween(5, 30));
                Schedule_override::create([
                    'doctor_id' => $doctor->id,
                    'override_date' => $futureDate->format('Y-m-d'),
                    'start_time' => $futureDate->copy()->setTime(9, 0),
                    'end_time' => $futureDate->copy()->setTime(12, 0),
                    'override_type' => 'time_off',
                    'reason' => fake()->randomElement(['Personal appointment', 'Conference', 'Training', 'Half day']),
                    'is_closed' => false,
                ]);
            }
        });

        // Patients & Appointments ----------------------------------------------
        $patients = Patient::factory()->count(20)->for($clinic)->create();
        $appointments = collect();
        $usedSlots = [];

        foreach ($patients as $patient) {
            $doctor = $doctors->random();
            $type = Appointment_type::inRandomOrder()->first();
            $slotsNeeded = $type->types ?? 1;
            $baseDuration = $doctor->appointment_duration * $slotsNeeded;

            $start = null;
            $attempts = 0;

            do {
                $day = fake()->dateTimeBetween('-20 days', '+20 days');
                $hour = fake()->randomElement([9, 10, 11, 12, 14, 15, 16]);
                $minute = fake()->randomElement([0, 15, 30, 45]);
                $start = $day->setTime($hour, $minute);
                $key = $clinic->id . '_' . $doctor->id . '_' . $start->format('Y-m-d H:i');
                $attempts++;
            } while (isset($usedSlots[$key]) && $attempts < 100);

            if ($attempts >= 100) {
                continue;
            }

            $usedSlots[$key] = true;
            $end = (clone $start)->modify("+{$baseDuration} minutes");
            $isPast = (clone now()->parse($start))->lt(now());
            $statusWeights = $isPast
                ? fake()->randomElement(['completed', 'completed', 'cancelled', 'no_show'])
                : fake()->randomElement(['scheduled', 'scheduled', 'confirmed']);

            $appointments->push(
                Appointment::factory()->create([
                    'clinic_id' => $clinic->id,
                    'doctor_id' => $doctor->id,
                    'patient_id' => $patient->id,
                    'appointment_type_id' => $type->id,
                    'start_time' => $start,
                    'end_time' => $end,
                    'status' => $statusWeights,
                    'visit_reason' => fake()->randomElement([
                        'Annual checkup',
                        'Persistent headache',
                        'Chest pain',
                        'Skin rash',
                        'Follow-up visit',
                        'Blood pressure monitoring',
                        'Vaccination',
                        'Lab results review',
                        'Joint pain',
                        'Respiratory infection',
                    ]),
                    'visit_in_time' => !$isPast ? null : fake()->boolean(80),
                    'requires_followup' => fake()->boolean(25),
                    'notes' => fake()->boolean(40) ? fake()->sentence() : null,
                ])
            );
        }

        // Patient Records linked to completed/confirmed appointments -----------
        $appointments->whereIn('status', ['completed', 'confirmed'])->each(function (Appointment $appointment) use ($clinic, $diseases) {
            $record = Patient_record::factory()->create([
                'clinic_id' => $clinic->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'diagnosis_summary' => fake()->randomElement([
                    'Patient shows improvement with current treatment',
                    'New symptoms require further investigation',
                    'Condition stable, continue current medication',
                    'Referral to specialist recommended',
                    'Lab results indicate need for medication adjustment',
                ]),
                'description' => fake()->boolean(60) ? fake()->paragraph() : null,
                'status' => fake()->randomElement(['open', 'closed', 'follow-up']),
                'notes' => fake()->boolean(50) ? fake()->sentence() : null,
            ]);

            // Attach 1-3 diseases
            $record->diseases()->sync(
                $diseases->random(rand(1, 3))->mapWithKeys(fn($d) => [
                    $d->id => [
                        'status' => fake()->randomElement(['active', 'resolved', 'chronic']),
                        'severity' => fake()->randomElement(['mild', 'moderate', 'severe']),
                    ]
                ])->toArray()
            );
        });

        // Invoices & Payments --------------------------------------------------
        $appointments->where('status', 'completed')->each(function (Appointment $appointment) use ($clinic, $paymentMethods) {
            $status = fake()->randomElement(['paid', 'paid', 'partially_paid', 'draft']);
            $invoice = Invoice::factory()->create([
                'clinic_id' => $clinic->id,
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'status' => $status,
                'total_cost' => fake()->randomFloat(2, 150, 900),
                'description' => fake()->sentence(),
            ]);

            if (in_array($status, ['paid', 'partially_paid'], true)) {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'payment_method_id' => $paymentMethods->random()->id,
                    'amount' => $status === 'paid' ? $invoice->total_cost : round($invoice->total_cost / 2, 2),
                    'paid_at' => fake()->dateTimeBetween('-15 days', 'now'),
                ]);
            }
        });
    }
}
