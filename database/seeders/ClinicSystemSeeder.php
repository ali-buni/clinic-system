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
use App\Models\Schedule_override;
use App\Models\Secretary;
use App\Models\User;
use App\Models\Work_hour;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ClinicSystemSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethods = $this->createPaymentMethods();
        $clinic = $this->createOwnerAndClinic();
        $isInitialSeed = !Room::where('clinic_id', $clinic->id)->exists();

        if ($isInitialSeed) {
            $rooms = $this->createRooms($clinic);
            $doctors = $this->createDoctors($clinic, $rooms);
            $this->createSecretary($clinic);
            $this->createWorkHours($doctors);
            $this->createScheduleOverrides($doctors);

            $patients = PatientInfo::factory()->count(20)->create();
            $appointments = $this->createAppointments($clinic, $doctors, $patients);

            $diseases = Disease::all();
            $medicines = Medicine::all();
            $this->createPatientRecordsAndPrescriptions($appointments, $clinic, $diseases, $medicines, $doctors);
            // $this->createInvoicesAndPayments($appointments, $clinic, $paymentMethods);
            $this->createAnalyticsTestInvoices($clinic, $patients, $appointments, $paymentMethods);
        } else {
            $this->command->info('ClinicSystemSeeder already seeded — skipping full data seed.');
        }
        $this->generateApiToken($clinic);
    }

    private function createPaymentMethods(): \Illuminate\Support\Collection
    {
        return collect([
            ['ar_name' => 'نقداً', 'en_name' => 'Cash', 'is_active' => true],
            ['ar_name' => 'بطاقة', 'en_name' => 'Card', 'is_active' => true],
            ['ar_name' => 'تحويل مصرفي', 'en_name' => 'Bank Transfer', 'is_active' => true],
        ])->map(fn(array $data) => Payment_method::firstOrCreate($data));
    }

    private function createOwnerAndClinic(): Clinic
    {
        $owner = User::firstOrCreate(
            ['phone' => '0951232317'],
            [
                'email' => 'aliboune184@gmail.com',
                'fname' => 'Clinic',
                'lname' => 'Owner',
                'gender' => 'male',
                'password' => bcrypt('password'),
            ]
        );
        $owner->assignRole('owner');

        return Clinic::firstOrCreate(
            ['user_id' => $owner->id],
            [
                'location' => '123 Main Street',
                'title' => 'Central Clinic',
                'phone' => '0951232317',
            ]
        );
    }

    private function createRooms(Clinic $clinic): \Illuminate\Support\Collection
    {
        return collect(range(1, 3))->map(fn(int $i) => Room::firstOrCreate(
            ['clinic_id' => $clinic->id, 'name' => 'Room ' . $i],
            []
        ));
    }

    private function createDoctors(Clinic $clinic, \Illuminate\Support\Collection $rooms): \Illuminate\Support\Collection
    {
        $doctorData = [
            ['fname' => 'Amira', 'lname' => 'Hassan', 'gender' => 'female'],
            ['fname' => 'Omar', 'lname' => 'Nasser', 'gender' => 'male'],
            ['fname' => 'Layla', 'lname' => 'Farah', 'gender' => 'female'],
        ];

        return collect($doctorData)->map(function (array $data, int $index) use ($clinic, $rooms) {
            $phone = '095123230' . ($index + 1);
            $user = User::firstOrCreate(
                ['phone' => $phone],
                [
                    'email' => "doctor{$index}@clinic.test",
                    'fname' => $data['fname'],
                    'lname' => $data['lname'],
                    'gender' => $data['gender'],
                    'password' => bcrypt('password'),
                ]
            );
            $user->assignRole('doctor');

            return Doctor::firstOrCreate(
                ['user_id' => $user->id, 'clinic_id' => $clinic->id],
                [
                    'room_id' => $rooms->get($index % $rooms->count())->id,
                    'appointment_duration' => 30,
                    'consultation_fee' => fake()->randomFloat(2, 120, 300),
                ]
            );
        });
    }

    private function createSecretary(Clinic $clinic): void
    {
        $user = User::firstOrCreate(
            ['phone' => '0900000000'],
            [
                'email' => 'secretary@clinic.test',
                'fname' => 'Sara',
                'lname' => 'Ali',
                'gender' => 'female',
                'password' => bcrypt('password'),
            ]
        );
        $user->assignRole('secretary');

        Secretary::firstOrCreate(
            ['user_id' => $user->id, 'clinic_id' => $clinic->id],
            []
        );
    }

    private function createWorkHours(\Illuminate\Support\Collection $doctors): void
    {
        $dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];
        $shifts = [
            ['start' => '09:00', 'end' => '13:00'],
            ['start' => '14:00', 'end' => '17:00'],
        ];

        foreach ($doctors as $doctor) {
            foreach ($dayNames as $dayIndex => $dayName) {
                $shift = $shifts[$dayIndex % count($shifts)];
                Work_hour::firstOrCreate(
                    ['doctor_id' => $doctor->id, 'day_of_week' => $dayIndex],
                    [
                        'start_time' => $shift['start'],
                        'end_time' => $shift['end'],
                        'max_patients_per_day' => fake()->numberBetween(8, 15),
                    ]
                );
            }
        }
    }

    private function createScheduleOverrides(\Illuminate\Support\Collection $doctors): void
    {
        $overrides = [
            ['doctor_id' => $doctors[0]->id, 'override_date' => now()->addDays(3)->format('Y-m-d'), 'is_closed' => true, 'reason' => 'إجازة شخصية'],
            ['doctor_id' => $doctors[1]->id, 'override_date' => now()->addDays(5)->format('Y-m-d'), 'start_time' => '10:00', 'end_time' => '14:00', 'is_closed' => false, 'reason' => 'دوام جزئي'],
            ['doctor_id' => $doctors->random()->id, 'override_date' => now()->subDays(2)->format('Y-m-d'), 'is_closed' => true, 'reason' => 'مرضية'],
        ];

        foreach ($overrides as $data) {
            Schedule_override::firstOrCreate(
                ['doctor_id' => $data['doctor_id'], 'override_date' => $data['override_date']],
                $data
            );
        }
    }

    private function createAppointments(Clinic $clinic, \Illuminate\Support\Collection $doctors, \Illuminate\Support\Collection $patients): \Illuminate\Support\Collection
    {
        $appointments = collect();
        $usedSlots = [];
        $appointmentTypes = Appointment_type::all();
        $patientsPerDoctor = ceil(20 / $doctors->count());

        foreach ($doctors as $doctor) {
            $workHours = $doctor->workHours;
            $doctorPatients = $patients->forPage($doctors->search($doctor) + 1, $patientsPerDoctor);

            foreach ($doctorPatients as $patient) {
                $workHour = $workHours->random();
                $dayOfWeek = $workHour->day_of_week;

                $date = $this->findNextDateForDay($dayOfWeek);
                if (!$date) continue;

                $start = \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $workHour->start_time);
                $end = \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $workHour->end_time);

                $slotKey = $doctor->id . '_' . $start->format('Y-m-d H:i');
                if (isset($usedSlots[$slotKey])) continue;
                $usedSlots[$slotKey] = true;

                $apptEnd = (clone $start)->addMinutes($doctor->appointment_duration);
                if ($apptEnd->greaterThan($end)) continue;

                $isPast = $start->isPast();
                $status = 'scheduled';

                if ($isPast) {
                    $status = fake()->randomElement(['completed', 'completed', 'completed', 'cancelled', 'no_show']);
                } else {
                    $status = fake()->randomElement(['scheduled', 'scheduled', 'scheduled', 'cancelled']);
                }

                $appointments->push(Appointment::factory()->create([
                    'clinic_id' => $clinic->id,
                    'doctor_id' => $doctor->id,
                    'patient_id' => $patient->id,
                    'appointment_type_id' => $appointmentTypes->random()->id,
                    'start_time' => $start,
                    'end_time' => $apptEnd,
                    'status' => $status,
                    'visit_reason' => fake()->sentence(3),
                    'visit_in_time' => fake()->boolean(80),
                ]));
            }
        }

        return $appointments;
    }

    private function findNextDateForDay(int $dayOfWeek): ?\Carbon\Carbon
    {
        $now = now();
        for ($i = -14; $i <= 14; $i++) {
            $date = (clone $now)->addDays($i);
            if ((int)$date->format('w') === $dayOfWeek) {
                return $date;
            }
        }
        return null;
    }

    private function createPatientRecordsAndPrescriptions(
        \Illuminate\Support\Collection $appointments,
        Clinic $clinic,
        \Illuminate\Support\Collection $diseases,
        \Illuminate\Support\Collection $medicines,
        \Illuminate\Support\Collection $doctors
    ): void {
        foreach ($appointments as $appointment) {
            if (!in_array($appointment->status, ['completed', 'no_show'])) continue;

            $record = Patient_record::factory()->create([
                'clinic_id' => $clinic->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'diagnosis_summary' => fake()->sentence(),
                'description' => fake()->paragraph(2),
                'status' => fake()->randomElement(['closed', 'follow-up']),
                'notes' => fake()->paragraph(),
            ]);

            $assignedDiseases = $diseases->random(fake()->numberBetween(1, 3));
            foreach ($assignedDiseases as $disease) {
                $record->diseases()->attach($disease->id, [
                    'status' => fake()->randomElement(['active', 'resolved', 'chronic']),
                    'severity' => fake()->randomElement(['mild', 'moderate', 'severe']),
                ]);
            }

            if ($appointment->status === 'completed' && fake()->boolean(70)) {
                $prescription = Prescription::create([
                    'patient_record_id' => $record->id,
                    'doctor_id' => $appointment->doctor_id,
                    'valid_until' => now()->addDays(fake()->numberBetween(7, 90)),
                    'issued_at' => $appointment->start_time,
                    'cost' => fake()->randomFloat(2, 10, 150),
                ]);

                $prescriptionMedicines = $medicines->random(fake()->numberBetween(1, 4));
                foreach ($prescriptionMedicines as $medicine) {
                    Prescription_item::create([
                        'prescription_id' => $prescription->id,
                        'medicine_id' => $medicine->id,
                        'dosage_instruction' => fake()->randomElement(['1 tablet daily', '1 tablet twice daily', '1 tablet 3 times daily', '2 tablets daily', '1 capsule daily']),
                        'frequency' => fake()->randomElement(['once daily', 'twice daily', 'three times daily', 'every 8 hours', 'every 12 hours']),
                        'duration' => fake()->randomElement(['5 days', '7 days', '10 days', '14 days', '30 days']),
                    ]);
                }
            }
        }
    }

    private function createInvoicesAndPayments(
        \Illuminate\Support\Collection $appointments,
        Clinic $clinic,
        \Illuminate\Support\Collection $paymentMethods
    ): void {
        foreach ($appointments as $appointment) {
            if ($appointment->status === 'cancelled') continue;

            $totalCost = fake()->randomFloat(2, 150, 900);
            $status = $appointment->status === 'completed' ? 'paid' : 'issued';
            $status = fake()->boolean(15) ? 'draft' : $status;

            $invoice = Invoice::factory()->create([
                'clinic_id' => $clinic->id,
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'status' => $status,
                'total_cost' => $totalCost,
                'description' => fake()->sentence(),
            ]);

            if (in_array($invoice->status, ['paid', 'partially_paid'], true)) {
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'payment_method_id' => $paymentMethods->random()->id,
                    'amount' => $invoice->status === 'paid' ? $totalCost : $totalCost / 2,
                    'paid_at' => $appointment->start_time,
                ]);
            }
        }
    }

    private function createAnalyticsTestInvoices(
        Clinic $clinic,
        \Illuminate\Support\Collection $patients,
        \Illuminate\Support\Collection $appo,
        \Illuminate\Support\Collection $paymentMethods
    ): void {
        $this->command->info('Seeding 100 analytics test invoices with doctor assignments...');

        $statuses = ['paid', 'paid', 'paid', 'paid', 'draft', 'void'];

        $batch = [];
        for ($i = 0; $i < 100; $i++) {
            $date = now()->subDays(fake()->numberBetween(0, 365))->startOfDay();
            $appt = $appo->random();
            $patient = $patients->random();

            $status = $statuses[array_rand($statuses)];
            Invoice::create([
                'clinic_id'      => $clinic->id,
                'patient_id'     => $patient->id,
                'appointment_id' => $appt->id,
                'invoice_number' => 'TST-' . strtoupper(Str::random(10)),
                'status'         => $status,
                'total_cost'     => fake()->randomFloat(2, 50, 2000),
                'description'    => 'Analytics test invoice #' . ($i + 1),
                'created_at'     => $date,
            ]);
            $appo->forget($appt);
        }

        // Create payments for paid invoices
        $paidInvoices = Invoice::where('clinic_id', $clinic->id)
            ->where('status', 'paid')
            ->where('description', 'like', 'Analytics test invoice%')
            ->get();

        foreach ($paidInvoices as $invoice) {
            if (fake()->boolean(70)) {
                Payment::create([
                    'invoice_id'        => $invoice->id,
                    'payment_method_id' => $paymentMethods->random()->id,
                    'amount'            => $invoice->total_cost,
                    'paid_at'           => $invoice->created_at,
                ]);
            }
        }

        $this->command->info('Created 100 analytics test invoices with doctor assignments.');
    }

    private function generateApiToken(Clinic $clinic): void
    {
        $owner = $clinic->owner;

        // Revoke old tokens to keep only the current one
        $owner->tokens()->where('name', 'seeder-token')->delete();

        $token = $owner->createToken('seeder-token')->plainTextToken;

        $this->command->info('╔══════════════════════════════════════════════════════╗');
        $this->command->info('║         CLINIC OWNER API TOKEN                      ║');
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info("║  Email:    {$owner->email}");
        $this->command->info("║  Phone:    {$owner->phone}");
        $this->command->info("║  Password: password");
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info("║  Token:");
        $this->command->info("║  {$token}");
        $this->command->info('╚══════════════════════════════════════════════════════╝');

        logger('Clinic owner API token generated', [
            'clinic_id' => $clinic->id,
            'owner_id'  => $owner->id,
            'token'     => $token,
        ]);
    }
}
