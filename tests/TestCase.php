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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    protected $seed = true;

    protected string $apiUrl = '/api';

    protected function uri(string $path): string
    {
        return $this->apiUrl . $path;
    }

    protected function v1uri(string $path): string
    {
        return '/api/v1' . $path;
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

        $this->ownerUser = User::role('owner')->first();
        $this->clinic = Clinic::where('user_id', $this->ownerUser->id)->first();
        $this->room = Room::where('clinic_id', $this->clinic->id)->first();

        $this->doctorUser = User::role('doctor')->first();
        $this->doctor = Doctor::where('user_id', $this->doctorUser->id)->first();
        $this->doctor->specialties()->sync([Specialty::first()->id => ['is_primary' => true]]);

        $this->secretaryUser = User::role('secretary')->first();
        $this->secretary = Secretary::where('user_id', $this->secretaryUser->id)->first();

        $this->patientUser = User::role('patient')->first();
        $this->patient = PatientInfo::where('user_id', $this->patientUser->id)->first();

        $this->appointmentType = Appointment_type::first();
        $this->appointment = Appointment::where('patient_id', $this->patient->id)->first();
        $this->patientRecord = Patient_record::where('patient_id', $this->patient->id)->first();
        $this->workHour = Work_hour::where('doctor_id', $this->doctor->id)->first();
        $this->medicine = Medicine::first();
        $this->disease = Disease::first();

        $this->ownerToken = $this->ownerUser->createToken('test')->plainTextToken;
        $this->doctorToken = $this->doctorUser->createToken('test')->plainTextToken;
        $this->secretaryToken = $this->secretaryUser->createToken('test')->plainTextToken;
        $this->patientToken = $this->patientUser->createToken('test')->plainTextToken;
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
        try {
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
        } catch (\ErrorException $e) {
            // Silently skip if fixture cannot be written (e.g. concurrent access)
        }
    }

    protected function saveResult(string $entity, string $case, string $method, string $endpoint, array $request, $response, ?string $notes = null): void
    {
        $dir = base_path("tests/Results/{$entity}");
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $data = [
            'entity' => $entity,
            'case' => $case,
            'method' => $method,
            'endpoint' => $endpoint,
            'request' => $request,
            'response' => [
                'status' => $response->status(),
                'headers' => ['content-type' => $response->headers->get('content-type')],
                'body' => $response->json(),
            ],
            'timestamp' => now()->toIso8601String(),
        ];
        if ($notes) {
            $data['notes'] = $notes;
        }
        file_put_contents(
            "{$dir}/{$case}.json",
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
