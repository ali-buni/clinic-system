<?php

namespace App\Services\Ai;

use App\Constants\Prompt;
use App\Jobs\LogActivityJob;
use App\Models\ChatMessage;
use App\Models\PatientInfo;
use App\Services\Ai\Contracts\AiProviderInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PatientChatbotService
{
    public function __construct(protected AiProviderInterface $ai) {}

    public function chat(int $patientInfoId, string $message, ?string $sessionId): array
    {
        $patient = PatientInfo::with([
            'appointments' => fn($q) => $q->latest(),
            'records' => fn($q) => $q->latest()->with(['diseases', 'prescriptions.items.medicine']),
            'user',
        ])->findOrFail($patientInfoId);

        $sessionId = $sessionId ?? ChatMessage::generateSessionId($patientInfoId, Auth::id());
        $context = $this->buildContext($patient);

        $history = ChatMessage::where('session_id', $sessionId)
            ->where('chattable_id', $patientInfoId)
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

        $messages[] = ['role' => 'user', 'content' => "Patient context: $context\n\nPatient query: <user_message>\n$message\n</user_message>"];

        $response = $this->ai->chat(
            messages: $messages,
            options: [
                'session_id' => $sessionId,
                'temperature' => 0.4,
                'options' => [
                    'num_ctx' => 3072,
                    'temperature' => 0.4,
                ],
            ]
        );

        if (! $response) {
            Log::error('PatientChatbotService: AI provider returned null');
            $responseText = 'I apologize, but I am unable to process your request at this time. Please contact the clinic directly.';
        } else {
            $responseText = $response;
        }

        $chatMessage = DB::transaction(function () use ($patientInfoId, $message, $responseText, $sessionId) {
            return ChatMessage::create([
                'user_id' => Auth::id(),
                'chattable_type' => PatientInfo::class,
                'chattable_id' => $patientInfoId,
                'message' => $message,
                'response' => $responseText,
                'session_id' => $sessionId,
            ]);
        });

        LogActivityJob::dispatch(
            logName: 'chat',
            description: 'chat message created',
            subjectType: ChatMessage::class,
            subjectId: $chatMessage->id,
            causerId: Auth::id(),
            extra: ['session_id' => $sessionId, 'patient_info_id' => $patientInfoId],
            eventName: 'created',
        );

        Log::channel('structured')->info('chat message created', [
            'chat_message_id' => $chatMessage->id,
            'session_id' => $sessionId,
            'patient_info_id' => $patientInfoId,
        ]);

        return [
            'response' => $responseText,
            'session_id' => $sessionId,
        ];
    }

    public function history()
    {
        return ChatMessage::where('user_id', Auth::id())
            ->select('session_id', 'message', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('session_id')
            ->map(fn($group) => [
                'session_id' => $group->first()->session_id,
                'title' => $group->first()->message,
                'last_message_at' => $group->last()->created_at,
            ])
            ->values();
    }

    public function messages(string $sessionId)
    {
        return ChatMessage::where('session_id', $sessionId)
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function clearHistory(?string $sessionId = null): int
    {
        $query = ChatMessage::where('user_id', Auth::id());

        if ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        $deletedCount = $query->delete();

        LogActivityJob::dispatch(
            logName: 'chat',
            description: 'chat history cleared',
            subjectType: ChatMessage::class,
            subjectId: null,
            causerId: Auth::id(),
            extra: ['session_id' => $sessionId, 'deleted_count' => $deletedCount],
            eventName: 'deleted',
        );

        Log::channel('structured')->info('chat history cleared', [
            'session_id' => $sessionId,
            'deleted_count' => $deletedCount,
        ]);

        return $deletedCount;
    }

    private function systemPrompt(string $text): string
    {
        return $this->isArabic($text) ? Prompt::CHAT_AR : Prompt::CHAT_EN;
    }

    private function buildContext(PatientInfo $patient): string
    {
        return json_encode([
            'patient_name' => ($patient->user?->fname . ' ' . $patient->user?->lname) ?? 'Valued Patient',
            'upcoming_appointments' => $patient->appointments->map(fn($a) => [
                'date' => $a->start_time?->format('Y-m-d'),
                'time' => $a->start_time?->format('H:i'),
                'status' => $a->status,
                'doctor' => ($a->doctor?->user?->fname . ' ' . $a->doctor?->user?->lname) ?? 'N/A',
            ]),
            'recent_records' => $patient->records->map(fn($r) => [
                'diagnosis' => $r->diagnosis_summary,
                'notes' => $r->notes,
                'diseases' => $r->diseases->map(fn($d) => [
                    'en' => $d->en_name,
                    'ar' => $d->ar_name,
                ]),
                'prescriptions' => $r->prescriptions->map(fn($p) => [
                    'issued_at' => $p->issued_at ? Carbon::parse($p->issued_at)->format('Y-m-d') : null,
                    'valid_until' => $p->valid_until ? Carbon::parse($p->valid_until)->format('Y-m-d') : null,
                    'notes' => $p->notes,
                    'items' => $p->items->map(fn($i) => [
                        'medicine' => $i->medicine?->en_name,
                        'medicine_ar' => $i->medicine?->ar_name,
                        'dosage' => $i->dosage_instruction,
                        'frequency' => $i->frequency,
                        'duration' => $i->duration,
                    ]),
                ]),
                'date' => $r->created_at?->format('Y-m-d'),
            ]),
        ], JSON_PRETTY_PRINT);
    }

    public function isArabic(string $text): bool
    {
        return preg_match('/\p{Arabic}/u', $text) === 1;
    }
}
