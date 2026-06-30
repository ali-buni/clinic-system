<?php

namespace App\Services\Analytics;

use App\Services\Ai\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Log;

class NLAService
{
    public function __construct(
        private readonly AiProviderInterface $ai,
        private readonly string $model = 'llama3.2',
    ) {}

    public function askAnalytics(string $question, string $context): string
    {
        $systemPrompt = "You are a data analyst AI assistant for a Medical Clinic Management System.
You will be given structured JSON data about the clinic and must answer questions based ONLY on this data.

The data contains:
- 'operations': list of doctors with their appointments_count, available_hours, and utilization_rate (percentage of time booked)
- 'financials': list of doctors with their total_revenue
- 'medical': list of top diseases with cases_count

RULES:
- Answer directly and clearly based on the data provided.
- If asked about utilization, look inside the 'operations' array and compare 'utilization_rate' values.
- Never say you don't have data if the data is clearly present in the JSON.
- Always mention the specific doctor name and their exact utilization_rate in your answer.

CLINIC DATA:
" . $context;

        try {
            $response = $this->ai->chat(
                messages: [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $question],
                ],
                options: [
                    'model' => $this->model,
                ]
            );

            if ($response === null) {
                Log::warning('NLAService: AI returned null');
                return 'عذراً، تعذر الاتصال بخدمة التحليل الذكية. الرجاء المحاولة لاحقاً.';
            }

            return $response;
        } catch (\Throwable $e) {
            Log::error('NLAService request failed', ['error' => $e->getMessage()]);
            return 'عذراً، حدث خطأ تقني أثناء الاتصال بخدمة التحليل الذكية.';
        }
    }
}
