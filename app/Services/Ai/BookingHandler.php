<?php

namespace App\Services\Ai;

use App\Models\Appointment_type;
use App\Models\Doctor;
use App\Models\Location;
use App\Models\Specialty;
use App\Services\AppointmentService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BookingHandler
{
    public function __construct(protected AppointmentService $appointmentService) {}

    public function getSpecialtyWithDoctors(int $specialtyId, ?int $clinicId, string $date): array
    {
        $specialty = Specialty::find($specialtyId);
        if (! $specialty) {
            return ['error' => 'Specialty not found', 'next_step' => 'error'];
        }

        return array_merge([
            'specialty' => [
                'id' => $specialty->id,
                'en_name' => $specialty->en_name,
                'ar_name' => $specialty->ar_name,
            ],
        ], $this->getDoctorsBySpecialty($specialtyId, $clinicId, $date), ['next_step' => 'select_doctor']);
    }

    public function getDoctorsBySpecialty(int $specialtyId, ?int $clinicId, string $date, ?string $location = null): array
    {
        if (! $this->isValidDate($date)) {
            return ['doctors' => [], 'message' => 'Invalid date format.'];
        }

        $query = Doctor::whereHas('specialties', fn ($q) => $q->where('specialty_id', $specialtyId))
            ->with(['user', 'clinic.location']);

        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
        }

        if ($location) {
            $locationLower = mb_strtolower($location);
            $query->whereHas('clinic', function ($q) use ($locationLower) {
                $q->where(function ($subQ) use ($locationLower) {
                    $subQ->WhereRaw('LOWER(location) LIKE ?', ["%{$locationLower}%"]);
                })->orWhereHas('location', function ($locQ) use ($locationLower) {
                    $locQ->whereRaw('LOWER(name) LIKE ?', ["%{$locationLower}%"])
                        ->orWhereRaw('LOWER(city) LIKE ?', ["%{$locationLower}%"])
                        ->orWhereRaw('LOWER(governorate) LIKE ?', ["%{$locationLower}%"])
                        ->orWhereRaw('LOWER(country) LIKE ?', ["%{$locationLower}%"]);
                });
            });
        }

        $doctors = $query->get();

        if ($doctors->isEmpty()) {
            return ['doctors' => [], 'message' => 'No doctors found for this specialty'.($location ? " in {$location}" : '')];
        }

        $result = [];
        foreach ($doctors as $doctor) {
            $slots = [];
            try {
                $slots = $this->appointmentService->getAvailableSlots($doctor->id, $date);
                $slots = array_map(fn ($s) => [
                    'start' => date('H:i', strtotime($s['start'])),
                    'end' => date('H:i', strtotime($s['end'])),
                ], $slots);
            } catch (\Throwable $e) {
                Log::warning("getAvailableSlots failed for doctor {$doctor->id}", ['error' => $e->getMessage()]);
            }

            $result[] = [
                'id' => $doctor->id,
                'name' => $doctor->user?->fname.' '.$doctor->user?->lname ?? 'Unknown',
                'bio' => $doctor->bio,
                'fee' => $doctor->consultation_fee,
                'clinic' => $doctor->clinic ? [
                    'id' => $doctor->clinic->id,
                    'title' => $doctor->clinic->title,
                    'location' => $doctor->clinic->location,
                ] : null,
                'available_slots' => $slots,
            ];
        }

        return ['doctors' => $result];
    }

    public function getDoctorSlots(int $doctorId, string $date): array
    {
        $doctor = Doctor::with('user')->find($doctorId);
        if (! $doctor) {
            return ['error' => 'Doctor not found', 'next_step' => 'error'];
        }

        if (! $this->isValidDate($date)) {
            return ['error' => 'Invalid date format.', 'next_step' => 'error'];
        }

        $slots = [];
        try {
            $rawSlots = $this->appointmentService->getAvailableSlots($doctorId, $date);
            $slots = array_map(fn ($s) => [
                'start' => date('H:i', strtotime($s['start'])),
                'end' => date('H:i', strtotime($s['end'])),
            ], $rawSlots);
        } catch (\Throwable $e) {
            Log::warning("getAvailableSlots failed for doctor {$doctorId}", ['error' => $e->getMessage()]);
        }

        return [
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->user?->fname.' '.$doctor->user?->lname ?? 'Unknown',
            ],
            'date' => $date,
            'available_slots' => $slots,
            'next_step' => 'select_time',
        ];
    }

    public function getDoctorSlotsForWeek(int $doctorId): array
    {
        $doctor = Doctor::with('user')->find($doctorId);
        if (! $doctor) {
            return ['error' => 'Doctor not found', 'next_step' => 'error'];
        }

        $weeklySlots = [];
        $startDate = Carbon::tomorrow();

        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $dayName = $startDate->copy()->addDays($i)->dayName;

            try {
                $rawSlots = $this->appointmentService->getAvailableSlots($doctorId, $date);
                $slots = array_map(fn ($s) => [
                    'start' => date('H:i', strtotime($s['start'])),
                    'end' => date('H:i', strtotime($s['end'])),
                ], $rawSlots);

                if (! empty($slots)) {
                    $weeklySlots[$date] = [
                        'day_name' => $dayName,
                        'slots' => $slots,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("getAvailableSlots failed for doctor {$doctorId} on {$date}", ['error' => $e->getMessage()]);
            }
        }

        return [
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->user?->fname.' '.$doctor->user?->lname ?? 'Unknown',
            ],
            'available_slots' => $weeklySlots,
            'next_step' => 'select_time',
        ];
    }

    public function findDoctorById(int $doctorId): ?Doctor
    {
        return Doctor::with('user')->find($doctorId);
    }

    public function findDoctorByName(string $doctorName): ?Doctor
    {
        $words = array_filter(explode(' ', trim($doctorName)));
        if (empty($words)) {
            return null;
        }

        $result = Doctor::whereHas('user', function ($q) use ($words) {
            $q->where(function ($sub) use ($words) {
                foreach ($words as $word) {
                    $sub->where(function ($inner) use ($word) {
                        $inner->whereRaw('LOWER(fname) LIKE ?', ["%{$word}%"])
                            ->orWhereRaw('LOWER(lname) LIKE ?', ["%{$word}%"]);
                    });
                }
            });
        })->with('user')->first();

        if ($result) {
            return $result;
        }

        return Doctor::whereHas('user', function ($q) use ($words) {
            $q->where(function ($sub) use ($words) {
                foreach ($words as $word) {
                    $sub->orWhereRaw('LOWER(fname) LIKE ?', ["%{$word}%"])
                        ->orWhereRaw('LOWER(lname) LIKE ?', ["%{$word}%"]);
                }
            });
        })->with('user')->first();
    }

    public function getAllLocations(): array
    {
        return Cache::remember('locations:all', 3600, function () {
            return Location::select('id', 'name', 'city', 'governorate', 'country')
                ->get()
                ->map(fn ($l) => [
                    'id' => $l->id,
                    'name' => $l->name,
                    'city' => $l->city,
                    'governorate' => $l->governorate,
                    'country' => $l->country,
                ])
                ->toArray();
        });
    }

    public function bookAppointment(
        int $doctorId,
        string $date,
        string $time,
        int $patientId,
        ?int $clinicId = null,
        ?int $typeId = null,
        ?string $reason = null,
    ): array {
        $doctor = Doctor::with('user')->find($doctorId);
        if (! $doctor) {
            return ['error' => 'Doctor not found', 'next_step' => 'error'];
        }

        $typeId = $typeId ?? 1;
        $appointmentType = Appointment_type::find($typeId);

        $validationError = $this->validateSlot($doctorId, $date, $time, $clinicId, $typeId);
        if ($validationError) {
            return $validationError;
        }

        $slotMultiplier = (int) ($appointmentType?->types ?? 1);
        $slotMinutes = (int) ($doctor->appointment_duration ?? 30);
        $totalMinutes = max(1, $slotMultiplier) * $slotMinutes;

        $startTime = $time;
        $endTime = date('H:i', strtotime($time) + $totalMinutes * 60);

        try {
            $appointment = $this->appointmentService->bookAppointment(
                doctorId: $doctorId,
                date: $date,
                startTime: $startTime,
                endTime: $endTime,
                attributes: array_filter([
                    'patient_id' => $patientId,
                    'clinic_id' => $clinicId ?? $doctor->clinic_id,
                    'room_id' => $doctor->room_id,
                    'appointment_type_id' => $typeId,
                    'visit_reason' => $reason,
                ]),
            );

            Log::channel('structured')->info('AI appointment booked successfully', [
                'appointment_id' => $appointment->id,
                'doctor_id' => $doctorId,
                'patient_id' => $patientId,
            ]);

            return [
                'appointment' => [
                    'id' => $appointment->id,
                    'doctor_name' => $doctor->user?->fname.' '.$doctor->user?->lname ?? 'Unknown',
                    'date' => $date,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'status' => $appointment->status,
                ],
                'next_step' => 'complete',
            ];
        } catch (\Throwable $e) {
            Log::channel('structured')->error('AppointmentAssistant booking failed', [
                'doctor_id' => $doctorId,
                'patient_id' => $patientId,
                'error' => $e->getMessage(),
            ]);

            return ['error' => $e->getMessage(), 'next_step' => 'retry'];
        }
    }

    public function validateSlot(int $doctorId, string $date, string $time, ?int $clinicId = null, ?int $typeId = null): ?array
    {
        $doctor = Doctor::with('user')->find($doctorId);
        if (! $doctor) {
            return ['error' => 'Doctor not found', 'next_step' => 'error'];
        }

        if ($clinicId && (int) $doctor->clinic_id !== (int) $clinicId) {
            return ['error' => 'The selected doctor does not belong to your clinic.', 'next_step' => 'error'];
        }

        if (! $this->isValidDate($date)) {
            return ['error' => 'Invalid or past date provided.', 'next_step' => 'error'];
        }

        if (! $this->isValidTime($time)) {
            return ['error' => 'Invalid time provided.', 'next_step' => 'error'];
        }

        if ($typeId !== null && ! Appointment_type::whereKey($typeId)->exists()) {
            return ['error' => 'Invalid appointment type.', 'next_step' => 'error'];
        }

        $available = $this->availableStartTimes($doctorId, $date);

        if ($available === null) {
            return ['error' => 'Unable to verify slot availability. Please try again.', 'next_step' => 'retry'];
        }

        if (! in_array($time, $available, true)) {
            return ['error' => 'The requested time is not available for this doctor.', 'next_step' => 'error'];
        }

        return null;
    }

    private function availableStartTimes(int $doctorId, string $date): ?array
    {
        try {
            $slots = $this->appointmentService->getAvailableSlots($doctorId, $date);
        } catch (\Throwable $e) {
            Log::warning("getAvailableSlots failed for doctor {$doctorId} on {$date}", ['error' => $e->getMessage()]);

            return null;
        }

        return array_map(fn ($s) => date('H:i', strtotime($s['start'])), $slots);
    }

    private function isValidDate(string $date): bool
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        $parsed = \DateTime::createFromFormat('Y-m-d', $date);
        $errors = \DateTime::getLastErrors();
        if (! $parsed || ($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
            return false;
        }

        return $date >= now()->toDateString();
    }

    private function isValidTime(string $time): bool
    {
        if (! preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $time)) {
            return false;
        }

        $parsed = \DateTime::createFromFormat('H:i', substr($time, 0, 5));

        return $parsed !== false && \DateTime::getLastErrors() === ['warning_count' => 0, 'error_count' => 0];
    }
}
