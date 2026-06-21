<?php

namespace Tests;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Room;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\PatientInfo;
use App\Models\Specialty;
use App\Models\Appointment_type;
use App\Models\Appointment;
use App\Models\Patient_record;
use App\Models\Work_hour;
use App\Models\Medicine;
use App\Models\Disease;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SpecialtySeeder;
use Database\Seeders\AppointmentTypesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    protected string $apiUrl = '/api';

    use RefreshDatabase;

    protected function uri(string $path): string
    {
        return $this->apiUrl . $path;
    }
    protected string $ownerToken;
    protected string $doctorToken;
    protected string $secretaryToken;
    protected string $patientToken;
    protected User $ownerUser;
    protected User $doctorUser;
    protected User $secretaryUser;
    protected User $patientUser;
    protected Clinic $clinic;
    protected Room $room;
    protected Doctor $doctor;
    protected Secretary $secretary;
    protected PatientInfo $patient;
    protected Appointment_type $appointmentType;
    protected Work_hour $workHour;
    protected Appointment $appointment;
    protected Patient_record $patientRecord;
    protected Medicine $medicine;
    protected Disease $disease;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SpecialtySeeder::class);
        $this->seed(AppointmentTypesSeeder::class);

        $this->clinic = Clinic::factory()->create();

        // Owner
        $this->ownerUser = User::factory()->create([
            'email' => 'owner@test.com',
            'phone' => '0900000001',
        ]);
        $this->ownerUser->assignRole('owner');
        $this->clinic->update(['user_id' => $this->ownerUser->id]);
        $this->ownerToken = $this->ownerUser->createToken('test')->plainTextToken;

        // Room
        $this->room = Room::factory()->create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Test Room',
        ]);

        // Doctor
        $this->doctorUser = User::factory()->create([
            'email' => 'doctor@test.com',
            'phone' => '0900000002',
        ]);
        $this->doctorUser->assignRole('doctor');
        $this->doctor = Doctor::factory()->create([
            'user_id' => $this->doctorUser->id,
            'clinic_id' => $this->clinic->id,
            'room_id' => $this->room->id,
            'appointment_duration' => 30,
            'consultation_fee' => 150,
        ]);
        $this->doctor->specialties()->sync([Specialty::first()->id => ['is_primary' => true]]);
        $this->doctorToken = $this->doctorUser->createToken('test')->plainTextToken;

        // Secretary
        $this->secretaryUser = User::factory()->create([
            'email' => 'secretary@test.com',
            'phone' => '0900000003',
        ]);
        $this->secretaryUser->assignRole('secretary');
        $this->secretary = Secretary::factory()->create([
            'user_id' => $this->secretaryUser->id,
            'clinic_id' => $this->clinic->id,
        ]);
        $this->secretary->rooms()->sync([$this->room->id]);
        $this->secretaryToken = $this->secretaryUser->createToken('test')->plainTextToken;

        // Patient
        $this->patientUser = User::factory()->create([
            'email' => 'patient@test.com',
            'phone' => '0900000004',
            'password' => Hash::make('password'),
        ]);
        $this->patientUser->assignRole('patient');
        $this->patient = PatientInfo::factory()->create([
            'user_id' => $this->patientUser->id,
            'clinic_id' => $this->clinic->id,
        ]);
        $this->patientToken = $this->patientUser->createToken('test')->plainTextToken;

        // Appointment Type
        $this->appointmentType = Appointment_type::first();

        // Work Hour for Doctor
        $this->workHour = Work_hour::factory()->create([
            'doctor_id' => $this->doctor->id,
            'day_of_week' => now()->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_active' => true,
            'max_patients_per_day' => 20,
            'break_start' => '13:00',
            'break_end' => '14:00',
        ]);

        // Appointment
        $this->appointment = Appointment::factory()->create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'room_id' => $this->room->id,
            'patient_id' => $this->patient->id,
            'appointment_type_id' => $this->appointmentType->id,
            'start_time' => now()->addDays(1)->setTime(10, 0, 0),
            'end_time' => now()->addDays(1)->setTime(10, 30, 0),
            'status' => 'scheduled',
            'visit_reason' => 'Checkup',
        ]);

        // Patient Record
        $this->patientRecord = Patient_record::factory()->create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'appointment_id' => $this->appointment->id,
            'diagnosis_summary' => 'Test diagnosis',
            'status' => 'open',
        ]);

        // Medicine
        $this->medicine = Medicine::factory()->create([
            'ar_name' => 'باراسيتامول',
            'en_name' => 'Paracetamol',
            'form' => 'tablet',
            'strength' => '500mg',
            'is_custom' => true,
        ]);

        // Disease
        $this->disease = Disease::factory()->create([
            'ar_name' => 'السكري',
            'en_name' => 'Diabetes',
            'disease_nature' => 'chronic',
            'is_custom' => true,
        ]);
    }

    protected function authHeaders(string $token): array
    {
        return [
            'Authorization' => "Bearer $token",
            'Accept' => 'application/json',
        ];
    }

    protected function saveFixture(string $domain, string $filename, mixed $response): void
    {
        $path = base_path("tests/Fixtures/api-responses/{$domain}/{$filename}.json");
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents(
            $path,
            json_encode(
                [
                    'status' => $response->status(),
                    'headers' => $response->headers->all(),
                    'body' => $response->json(),
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            )
        );
    }
}
