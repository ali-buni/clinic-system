<?php

namespace App\Services\Ai;

use App\constant\Prompt;
use App\Models\Patient_record;
use App\Services\Ai\Contracts\AiProviderInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MedicalReportService
{
    public function __construct(protected AiProviderInterface $ai) {}

    public function summarize(int $recordId)
    {
        $record = Patient_record::with([
            'doctor.user',
            'patient.user',
            'appointment',
            'diseases' => fn($q) => $q->withPivot(['status', 'severity']),
            'prescriptions.items.medicine',
        ])->find($recordId);

        if (!$record) {
            throw new ModelNotFoundException;
        }

        $context = $this->buildContext($record);

        $response = $this->ai->chat(
            messages: [
                ['role' => 'system', 'content' => $this->systemPrompt()],
                ['role' => 'user', 'content' => $context],
            ],
            options: [
                'format' => 'json',
                'options' => [
                    'num_ctx' => 3072,
                    'temperature' => 0.2,
                    'top_p' => 0.9,
                ],
            ]
        );

        if (!$response) {
            Log::error('MedicalReportService: Ollama returned null');
            return null;
        }

        return $this->parseResponse($response, $record);
    }

    private function systemPrompt(): string
    {
        return Auth::user()->hasRole('doctor') ? Prompt::DOCTOR_SUMMARY_PROMPT : Prompt::PATIENT_SUMMARY_PROMPT;
    }

    private function buildContext(Patient_record $record): string
    {
        return json_encode([
            'patient' => [
                'name' => $record->patient?->user?->fname . ' ' . $record->patient?->user?->lname ?? 'N/A',
                'age' => Carbon::parse($record->patient?->user?->dob)->diff(Carbon::now())->years() ?? 'N/A',
            ],
            'doctor' => $record->doctor?->user?->fname . ' ' . $record->doctor?->user?->lname ?? 'N/A',
            'diagnosis_summary' => $record->diagnosis_summary,
            'description' => $record->description,
            'notes' => $record->notes,
            'status' => $record->status,
            'diseases' => $record->diseases->map(fn($d) => [
                'name' => $d->en_name ?? $d->ar_name,
                'code' => $d->code,
                'status' => $d->pivot?->status,
                'severity' => $d->pivot?->severity,
            ]),
            'prescriptions' => $record->prescriptions->map(fn($p) => [
                'items' => $p->items->map(fn($i) => [
                    'medicine' => $i->medicine?->en_name ?? $i->medicine?->ar_name,
                    'dosage' => $i->dosage_instruction,
                    'frequency' => $i->frequency,
                    'duration' => $i->duration,
                ]),
            ]),
            'appointment_date' => $record->appointment?->start_time,
        ], JSON_PRETTY_PRINT);
    }

    private function parseResponse(string $response, Patient_record $record): array
    {
        $parsed = $this->ai->parseJson($response);

        if (!$parsed) {
            return [
                'summary_en' => $response,
                'summary_ar' => null,
                'key_findings' => [],
                'recommendations' => [],
                'record_id' => $record->id,
            ];
        }

        return array_merge($parsed, [
            'record_id' => $record->id,
            'patient_name' => $record->patient?->user?->fname . ' ' . $record->patient?->user?->lname ?? 'N/A',
        ]);
    }
}
