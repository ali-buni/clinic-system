<?php

namespace App\Services\Ai;

use App\constant\Prompt;
use App\Models\ChatMessage;
use App\Models\PatientInfo;
use App\Services\Ai\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PatientChatbotService
{
    public function __construct(protected AiProviderInterface $ai) {}

    public function chat(int $patientInfoId, string $message, ?string $sessionId): array
    {
        $patient = PatientInfo::with([
            'appointments' => fn($q) => $q->latest()->limit(5),
            'records' => fn($q) => $q->latest()->limit(5),
            'user',
        ])->findOrFail($patientInfoId);

        $sessionId = $sessionId ?? uniqid('chat_', true);
        $context = $this->buildContext($patient);

        $history = ChatMessage::where('session_id', $sessionId)
            ->latest()
            ->limit(10)
            ->get()
            ->reverse();

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt($message)],
        ];

        foreach ($history as $msg) {
            $messages[] = ['role' => 'user', 'content' => $msg->message];
            $messages[] = ['role' => 'assistant', 'content' => $msg->response];
        }

        $messages[] = ['role' => 'user', 'content' => "Patient context: $context\n\nPatient query: $message"];

        $response = $this->ai->chat(
            messages: $messages,
            options: [
                'options' => [
                    'num_ctx' => 3072,
                    'temperature' => 0.4,
                ],
            ]
        );

        if (!$response) {
            Log::error('PatientChatbotService: Ollama returned null');
            $responseText = 'I apologize, but I am unable to process your request at this time. Please contact the clinic directly.';
        } else {
            $responseText = $response;
        }

        DB::transaction(function () use ($patientInfoId, $message, $responseText, $sessionId) {
            ChatMessage::create([
                'user_id' => Auth::id(),
                'chattable_type' => PatientInfo::class,
                'chattable_id' => $patientInfoId,
                'message' => $message,
                'response' => $responseText,
                'session_id' => $sessionId,
            ]);
        });

        return [
            'response' => $responseText,
            'session_id' => $sessionId,
        ];
    }

    public function history(string $sessionId)
    {
        return ChatMessage::where('session_id', $sessionId)
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    private function systemPrompt(string $text): string
    {
        return isArabic($text) ? Prompt::CHAT_AR : Prompt::CHAT_EN;
    }

    private function buildContext(PatientInfo $patient): string
    {
        return json_encode([
            'patient_name' => $patient->user?->fname . ' ' . $patient->user?->lname ?? 'Valued Patient',
            'upcoming_appointments' => $patient->appointments->map(fn($a) => [
                'date' => $a->start_time?->format('Y-m-d'),
                'time' => $a->start_time?->format('H:i'),
                'status' => $a->status,
                'doctor' => $a->doctor?->user?->name ?? 'N/A',
            ]),
        ], JSON_PRETTY_PRINT);
    }

    public function isArabic(string $text): bool
    {
        return preg_match('/\p{Arabic}/u', $text) === 1;
    }
}
