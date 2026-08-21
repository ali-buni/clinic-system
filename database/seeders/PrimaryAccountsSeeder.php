<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Appointment_type;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Disease;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Medicine;
use App\Models\PatientInfo;
use App\Models\Patient_record;
use App\Models\Payment;
use App\Models\Payment_method;
use App\Models\Prescription;
use App\Models\Prescription_item;
use App\Models\Room;
use App\Models\Specialty;
use App\Models\User;
use App\Models\Work_hour;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PrimaryAccountsSeeder extends Seeder
{
    private const DOCTOR_EMAIL = 'aliboune184@gmail.com';
    private const PATIENT_EMAIL = 'alialbuni185@gmail.com';
    private const PASSWORD = 'password';

    public function run(): void
    {
        $clinic = Clinic::first();
        if (!$clinic) return;

        $clinical = require __DIR__ . '/../data/clinical_options.php';
        $statusMaps = require __DIR__ . '/../data/status_maps.php';
        $prescriptionOpts = require __DIR__ . '/../data/prescription_options.php';

        [$doctorUser, $doctor] = $this->seedDoctor($clinic);
        [$patientUser, $patientInfo] = $this->seedPatient();

        $appointments = $this->seedAppointments($clinic, $doctor, $patientInfo);

        $this->seedRecords($clinic, $doctor, $appointments, $clinical, $prescriptionOpts);
        $this->seedInvoices($clinic, $doctor, $appointments, $statusMaps['invoice_map']);

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════╗');
        $this->command->info('║  Primary Accounts Credentials                        ');
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info('║  DOCTOR                                              ');
        $this->command->info('║  Email:    ' . str_pad(self::DOCTOR_EMAIL, 44));
        $this->command->info('║  Password: ' . str_pad(self::PASSWORD, 44));
        $this->command->info('║  PATIENT                                             ');
        $this->command->info('║  Email:    ' . str_pad(self::PATIENT_EMAIL, 44));
        $this->command->info('║  Password: ' . str_pad(self::PASSWORD, 44));
        $this->command->info('╚══════════════════════════════════════════════════════╝');
        $this->command->info('');
    }

    private function findOrCreateUser(string $email, string $phone, array $attributes): User
    {
        $user = User::where('email_hash', User::hashEmail($email))->first()
            ?? User::create(array_merge(['email' => $email, 'phone' => $phone], $attributes));

        $user->forceFill([
            'email_verified_at' => $user->email_verified_at ?? now(),
            'phone_verified_at' => $user->phone_verified_at ?? now(),
        ])->save();

        return $user;
    }

    private function seedDoctor(Clinic $clinic): array
    {
        $doctorUser = $this->findOrCreateUser(self::DOCTOR_EMAIL, '0933000001', [
            'fname' => 'Ali',
            'lname' => 'Boune',
            'gender' => 'male',
            'dob' => '1990-05-14',
            'password' => Hash::make(self::PASSWORD),
        ]);
        $doctorUser->assignRole('doctor');

        $room = Room::where('clinic_id', $clinic->id)->orderBy('id')->first();

        $doctor = Doctor::firstOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'clinic_id' => $clinic->id,
                'room_id' => $room?->id,
                'bio' => 'Experienced physician dedicated to comprehensive patient care and evidence-based treatment.',
                'appointment_duration' => 30,
                'consultation_fee' => 150.00,
            ]
        );

        $specialties = Specialty::all();
        if ($specialties->isNotEmpty()) {
            $syncData = [];
            foreach ($specialties as $i => $specialty) {
                $syncData[$specialty->id] = ['is_primary' => $i === 0];
            }
            $doctor->specialties()->sync($syncData);
        }

        foreach (range(0, 6) as $day) {
            Work_hour::firstOrCreate(
                ['doctor_id' => $doctor->id, 'day_of_week' => $day],
                [
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'break_start' => '13:00',
                    'break_end' => '14:00',
                    'max_patients_per_day' => 20,
                    'is_active' => true,
                ]
            );
        }

        return [$doctorUser, $doctor];
    }

    private function seedPatient(): array
    {
        $patientUser = $this->findOrCreateUser(self::PATIENT_EMAIL, '0933000002', [
            'fname' => 'Ali',
            'lname' => 'Albuni',
            'gender' => 'male',
            'dob' => '1998-03-22',
            'password' => Hash::make(self::PASSWORD),
        ]);
        $patientUser->assignRole('patient');

        $patientInfo = PatientInfo::firstOrCreate(
            ['user_id' => $patientUser->id],
            [
                'nationality' => 'Syria',
                'address' => 'Mazzeh District, Damascus, Syria',
                'marital_status' => 'single',
                'emergency_phone' => '0944111222',
                'allergies' => 'Penicillin',
                'chronic_conditions' => 'Mild asthma',
                'career' => 'Software Engineer',
                'blood_type' => 'O+',
            ]
        );

        return [$patientUser, $patientInfo];
    }

    private function seedAppointments(Clinic $clinic, Doctor $doctor, PatientInfo $patientInfo): array
    {
        $apptType = Appointment_type::orderBy('id')->first();

        $slots = [
            'completed' => [
                'start' => Carbon::now()->subDays(7)->setTime(10, 0),
                'visit_reason' => 'Persistent headache and fatigue',
                'extra' => ['visit_in_time' => true],
            ],
            'cancelled' => [
                'start' => Carbon::now()->subDays(3)->setTime(11, 0),
                'visit_reason' => 'Routine check-up',
                'extra' => ['cancel_reason' => 'Patient requested reschedule due to work conflict'],
            ],
            'scheduled' => [
                'start' => Carbon::now()->addDays(3)->setTime(10, 30),
                'visit_reason' => 'Follow-up consultation',
                'extra' => [],
            ],
        ];

        $appointments = [];
        foreach ($slots as $status => $slot) {
            $appointments[$status] = Appointment::firstOrCreate(
                [
                    'clinic_id' => $clinic->id,
                    'doctor_id' => $doctor->id,
                    'start_time' => $slot['start'],
                ],
                array_merge([
                    'room_id' => $doctor->room_id,
                    'patient_id' => $patientInfo->id,
                    'appointment_type_id' => $apptType?->id,
                    'end_time' => (clone $slot['start'])->addMinutes($doctor->appointment_duration ?? 30),
                    'status' => $status,
                    'visit_reason' => $slot['visit_reason'],
                ], $slot['extra'])
            );
        }

        return $appointments;
    }

    private function seedRecords(Clinic $clinic, Doctor $doctor, array $appointments, array $clinical, array $prescriptionOpts): void
    {
        $completed = Patient_record::firstOrCreate(
            ['appointment_id' => $appointments['completed']->id],
            [
                'clinic_id' => $clinic->id,
                'patient_id' => $appointments['completed']->patient_id,
                'doctor_id' => $doctor->id,
                'diagnosis_summary' => 'Tension headache with mild hypertension',
                'description' => 'Patient presented with recurring headaches for two weeks. Blood pressure slightly elevated. Prescribed medication and advised lifestyle adjustments.',
                'status' => 'closed',
                'notes' => 'Recheck blood pressure in one month.',
            ]
        );

        $disease = Disease::inRandomOrder()->first();
        if ($disease) {
            $completed->diseases()->syncWithoutDetaching([
                $disease->id => [
                    'status' => 'active',
                    'severity' => 'moderate',
                ],
            ]);
        }

        $medicine = Medicine::where('id', '!=', $clinical['unprescribable_medicine_id'])
            ->inRandomOrder()
            ->first();
        if ($medicine) {
            $prescription = Prescription::firstOrCreate(
                ['patient_record_id' => $completed->id],
                [
                    'doctor_id' => $doctor->id,
                    'valid_until' => Carbon::parse($appointments['completed']->start_time)->addDays(30),
                    'issued_at' => $appointments['completed']->start_time,
                    'cost' => 45.50,
                    'notes' => 'Take after meals with plenty of water.',
                ]
            );

            Prescription_item::firstOrCreate(
                ['prescription_id' => $prescription->id, 'medicine_id' => $medicine->id],
                [
                    'dosage_instruction' => $prescriptionOpts['dosage_instructions'][0],
                    'frequency' => $prescriptionOpts['frequencies'][0],
                    'duration' => $prescriptionOpts['durations'][0],
                ]
            );
        }

        Patient_record::firstOrCreate(
            ['appointment_id' => $appointments['scheduled']->id],
            [
                'clinic_id' => $clinic->id,
                'patient_id' => $appointments['scheduled']->patient_id,
                'doctor_id' => $doctor->id,
                'diagnosis_summary' => null,
                'description' => 'Initial consultation scheduled - pending examination.',
                'status' => 'open',
                'notes' => null,
            ]
        );
    }

    private function seedInvoices(Clinic $clinic, Doctor $doctor, array $appointments, array $invoiceMap): void
    {
        foreach ($appointments as $status => $appointment) {
            if ($appointment->invoices()->exists()) continue;

            $invoiceStatus = $invoiceMap[$status];
            $totalCost = $doctor->consultation_fee ?? 150.00;

            $invoice = Invoice::create([
                'clinic_id' => $clinic->id,
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'status' => $invoiceStatus,
                'total_cost' => $totalCost,
                'description' => 'Appointment invoice - ' . $status,
                'created_at' => $appointment->start_time,
            ]);

            Item::inRandomOrder()->take(2)->each(function ($item) use ($invoice) {
                $invoice->items()->attach($item->id, [
                    'quantity' => 1,
                    'price' => fake()->randomFloat(2, 10, 100),
                ]);
            });

            if ($invoiceStatus === 'paid') {
                $method = Payment_method::orderBy('id')->first();
                if ($method) {
                    Payment::firstOrCreate(
                        ['invoice_id' => $invoice->id],
                        [
                            'payment_method_id' => $method->id,
                            'amount' => $totalCost,
                            'refunded_amount' => 0,
                            'paid_at' => $appointment->start_time,
                        ]
                    );
                }
            }
        }
    }
}
