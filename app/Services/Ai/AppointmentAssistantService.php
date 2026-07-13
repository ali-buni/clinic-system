<?php

namespace App\Services\Ai;

use App\Constants\Prompt;
use App\Models\Specialty;
use App\Services\Ai\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AppointmentAssistantService
{
    public function __construct(
        protected AiProviderInterface $ai,
        protected BookingHandler $booking,
    ) {}

    public function processRequest(array $data): array
    {
        $query = $data['query'] ?? '';
        $patientId = $data['patient_id'] ?? auth()->user()?->patientProfile?->id;
        $clinicId = $data['clinic_id'] ?? auth()->user()?->clinic_id;

        Log::channel('structured')->info('AppointmentAssistant: incoming request', [
            'query' => $query,
            'patient_id' => $patientId,
            'clinic_id' => $clinicId,
            'user_id' => auth()->id(),
        ]);

        $parsed = $this->parseWithAi($query);

        if (!$parsed || empty($parsed['action'])) {
            Log::channel('structured')->warning('AppointmentAssistant: AI returned no action', [
                'query' => $query,
                'parsed' => $parsed,
            ]);

            return [
                'result' => [
                    'action' => 'ask_clarification',
                    'data' => [],
                ],
                'next_steps' => [
                    ['action' => 'describe_symptoms', 'description' => 'Describe your symptoms'],
                    ['action' => 'select_specialty', 'description' => 'Choose a medical specialty'],
                ],
                'message' => 'Could not understand your request. Please describe your symptoms or tell us what you need.',
            ];
        }

        Log::channel('structured')->info('AppointmentAssistant: AI parsed action', [
            'query' => $query,
            'action' => $parsed['action'],
            'parsed' => $parsed,
        ]);

        $result = $this->executeAction($parsed, $patientId, $clinicId);

        Log::channel('structured')->info('AppointmentAssistant: response sent', [
            'query' => $query,
            'action' => $parsed['action'],
            'result_action' => $result['result']['action'] ?? 'unknown',
        ]);

        return $result;
    }

    private function parseWithAi(string $query): ?array
    {
        $allSpecialties = Cache::remember('specialties:all', 3600, function () {
            return Specialty::select('id', 'en_name', 'ar_name')->get();
        });

        $specialtyList = $allSpecialties->map(fn($s) => [
            'id' => $s->id,
            'en_name' => $s->en_name,
            'ar_name' => $s->ar_name,
        ])->values()->toJson(JSON_PRETTY_PRINT);

        $locations = $this->booking->getAllLocations();
        $locationList = collect($locations)->map(fn($l) => "{$l['name']} - {$l['city']} ({$l['governorate']}) ({$l['country']})")->implode("\n");

        $isAr = $this->isArabic($query);
        $systemPrompt = $isAr
            ? Prompt::APPOINTMENT_ASSISTANT_AR($specialtyList, $locationList)
            : Prompt::APPOINTMENT_ASSISTANT_EN($specialtyList, $locationList);

        $parsed = $this->ai->chatJson(
            messages: [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $query],
            ],
            options: [
                'options' => [
                    'temperature' => 0.3,
                ],
            ]
        );

        return $parsed;
    }

    private function executeAction(array $parsed, ?int $patientId, ?int $clinicId): array
    {
        $action = $parsed['action'];
        $isAr = !empty($parsed['language']) && $parsed['language'] === 'ar';

        Log::channel('structured')->info('AppointmentAssistant: executing action', [
            'action' => $action,
            'parsed' => $parsed,
        ]);

        switch ($action) {
            case 'suggest_specialties':
                return $this->handleSuggestSpecialties($parsed, $isAr);

            case 'show_doctors':
                return $this->handleShowDoctors($parsed, $clinicId);

            case 'show_slots':
                return $this->handleShowSlots($parsed);

            case 'book_appointment':
                return $this->handleBookAppointment($parsed, $patientId, $clinicId);

            case 'ask_clarification':
                return [
                    'result' => ['action' => 'ask_clarification', 'data' => []],
                    'next_steps' => [
                        ['action' => 'describe_symptoms', 'description' => $isAr ? 'صف أعراضك' : 'Describe your symptoms'],
                        ['action' => 'select_specialty', 'description' => $isAr ? 'اختر تخصصاً طبياً' : 'Choose a medical specialty'],
                    ],
                    'message' => $parsed['message'] ?? ($isAr ? 'كيف يمكنني مساعدتك؟' : 'How can I help you?'),
                ];

            default:
                return [
                    'result' => ['action' => 'ask_clarification', 'data' => []],
                    'next_steps' => [],
                    'message' => 'Unknown action.',
                ];
        }
    }

    private function handleSuggestSpecialties(array $parsed, bool $isAr): array
    {
        $specialties = $parsed['specialties'] ?? [];
        $symptoms = $parsed['extracted_symptoms'] ?? [];

        if (empty($specialties)) {
            return [
                'result' => ['action' => 'suggest_specialties', 'data' => ['specialties' => []]],
                'next_steps' => [
                    ['action' => 'describe_symptoms', 'description' => $isAr ? 'اوصف أعراضك بشكل أوضح' : 'Describe your symptoms more clearly'],
                ],
                'message' => $isAr ? 'لم نتمكن من تحديد التخصص المناسب. يرجى وصف أعراضك بشكل أوضح.' : 'Could not determine the appropriate specialty. Please describe your symptoms more clearly.',
            ];
        }

        $nextSteps = [];
        foreach ($specialties as $s) {
            $nextSteps[] = [
                'action' => 'select_specialty',
                'description' => $isAr ? ($s['ar_name'] ?? $s['en_name'] ?? 'Unknown') : ($s['en_name'] ?? 'Unknown'),
                'params' => ['specialty_id' => $s['id']],
            ];
        }

        $message = $isAr
            ? 'بناءً على أعراضك، أقترح التخصصات التالية:'
            : 'Based on your symptoms, we suggest the following specialties:';

        return [
            'result' => [
                'action' => 'suggest_specialties',
                'data' => [
                    'specialties' => $specialties,
                    'extracted_symptoms' => $symptoms,
                ],
            ],
            'next_steps' => $nextSteps,
            'message' => $message,
        ];
    }

    private function handleShowDoctors(array $parsed, ?int $clinicId): array
    {
        $specialtyId = $parsed['specialty_id'] ?? null;
        $location = $parsed['location'] ?? null;

        if (!$specialtyId) {
            return [
                'result' => ['action' => 'ask_clarification', 'data' => []],
                'next_steps' => [],
                'message' => 'Please specify a specialty.',
            ];
        }

        $date = $parsed['date'] ?? now()->addDay()->format('Y-m-d');
        $result = $this->booking->getDoctorsBySpecialty((int) $specialtyId, $clinicId, $date, $location);

        $nextSteps = [];
        if (!empty($result['doctors'])) {
            foreach ($result['doctors'] as $doctor) {
                $nextSteps[] = [
                    'action' => 'select_doctor',
                    'description' => $doctor['name'],
                    'params' => ['doctor_id' => $doctor['id']],
                ];
            }
        }

        return [
            'result' => [
                'action' => 'show_doctors',
                'data' => $result,
            ],
            'next_steps' => $nextSteps,
            'message' => empty($result['doctors'])
                ? ($result['message'] ?? 'No doctors found.')
                : 'Available doctors:',
        ];
    }

    private function handleShowSlots(array $parsed): array
    {
        $doctorId = $parsed['doctor_id'] ?? null;
        $doctorName = $parsed['doctor_name'] ?? null;

        if (!$doctorId && $doctorName) {
            $doctor = $this->booking->findDoctorByName($doctorName);
            if ($doctor) {
                $doctorId = $doctor->id;
            } else {
                return [
                    'result' => ['action' => 'ask_clarification', 'data' => []],
                    'next_steps' => [],
                    'message' => "Doctor '{$doctorName}' not found. Please check the name.",
                ];
            }
        }

        if (!$doctorId) {
            return [
                'result' => ['action' => 'ask_clarification', 'data' => []],
                'next_steps' => [],
                'message' => 'Please specify a doctor.',
            ];
        }

        $range = $parsed['range'] ?? 'week';
        $date = $parsed['date'] ?? null;
        $time = $parsed['time'] ?? null;

        if ($range === 'week' || (!$date && !$time)) {
            $result = $this->booking->getDoctorSlotsForWeek((int) $doctorId);
        } elseif ($date) {
            $result = $this->booking->getDoctorSlots((int) $doctorId, $date);
        } else {
            $result = $this->booking->getDoctorSlotsForWeek((int) $doctorId);
        }

        $nextSteps = [];
        if (!empty($result['available_slots'])) {
            if (isset($result['available_slots'][0])) {
                foreach ($result['available_slots'] as $slot) {
                    $nextSteps[] = [
                        'action' => 'select_time',
                        'description' => "{$slot['start']} - {$slot['end']}",
                        'params' => ['start_time' => $slot['start']],
                    ];
                }
            } else {
                foreach ($result['available_slots'] as $dateKey => $dayData) {
                    foreach ($dayData['slots'] as $slot) {
                        $nextSteps[] = [
                            'action' => 'select_time',
                            "description" => "{$dateKey} ({$dayData['day_name']}) {$slot['start']} - {$slot['end']}",
                            'params' => ['date' => $dateKey, 'start_time' => $slot['start']],
                        ];
                    }
                }
            }
        }

        return [
            'result' => [
                'action' => 'show_slots',
                'data' => $result,
            ],
            'next_steps' => $nextSteps,
            'message' => empty($result['available_slots'])
                ? 'No available slots found.'
                : 'Available time slots:',
        ];
    }

    private function handleBookAppointment(array $parsed, ?int $patientId, ?int $clinicId): array
    {
        $doctorId = $parsed['doctor_id'] ?? null;
        $doctorName = $parsed['doctor_name'] ?? null;
        $date = $parsed['date'] ?? null;
        $time = $parsed['time'] ?? $parsed['start_time'] ?? null;

        if (!$doctorId && $doctorName) {
            $doctor = $this->booking->findDoctorByName($doctorName);
            if ($doctor) {
                $doctorId = $doctor->id;
            } else {
                return [
                    'result' => ['action' => 'ask_clarification', 'data' => []],
                    'next_steps' => [],
                    'message' => "Doctor '{$doctorName}' not found.",
                ];
            }
        }

        if (!$doctorId || !$date || !$time) {
            $missing = [];
            if (!$doctorId) $missing[] = 'doctor';
            if (!$date) $missing[] = 'date';
            if (!$time) $missing[] = 'time';

            return [
                'result' => ['action' => 'ask_clarification', 'data' => []],
                'next_steps' => [],
                'message' => 'Missing information to book: ' . implode(', ', $missing) . '.',
            ];
        }

        if (!$patientId) {
            return [
                'result' => ['action' => 'ask_clarification', 'data' => []],
                'next_steps' => [],
                'message' => 'Please log in to book an appointment.',
            ];
        }

        $result = $this->booking->bookAppointment(
            doctorId: (int) $doctorId,
            date: $date,
            time: $time,
            patientId: (int) $patientId,
            clinicId: $clinicId,
            typeId: isset($parsed['appointment_type_id']) ? (int) $parsed['appointment_type_id'] : null,
            reason: $parsed['visit_reason'] ?? null,
        );

        if (!empty($result['error'])) {
            Log::channel('structured')->warning('AppointmentAssistant: booking failed', [
                'doctor_id' => $doctorId,
                'date' => $date,
                'time' => $time,
                'patient_id' => $patientId,
                'error' => $result['error'],
            ]);

            return [
                'result' => ['action' => 'book_appointment', 'data' => $result],
                'next_steps' => [
                    ['action' => 'retry', 'description' => 'Try booking again'],
                ],
                'message' => $result['error'],
            ];
        }

        Log::channel('structured')->info('AppointmentAssistant: booking successful', [
            'doctor_id' => $doctorId,
            'date' => $date,
            'time' => $time,
            'patient_id' => $patientId,
            'appointment_id' => $result['appointment']['id'] ?? null,
        ]);

        return [
            'result' => [
                'action' => 'book_appointment',
                'data' => $result,
            ],
            'next_steps' => [
                ['action' => 'view_appointment', 'description' => 'View appointment details'],
                ['action' => 'new_booking', 'description' => 'Book another appointment'],
            ],
            'message' => 'Appointment booked successfully!',
        ];
    }

    private function isArabic(string $text): bool
    {
        return preg_match('/\p{Arabic}/u', $text) === 1;
    }
}
