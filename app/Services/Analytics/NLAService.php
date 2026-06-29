<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NLAService
{
    public function __construct(
        private readonly string $url = 'http://127.0.0.1:11434/api/generate',
        private readonly string $model = 'llama3.2',
        private readonly int $timeout = 120,
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
            $response = Http::timeout($this->timeout)->post($this->url, [
                'model'  => $this->model,
                'prompt' => $systemPrompt . "\n\nUSER QUESTION: " . $question,
                'stream' => false,
            ]);

            if (!$response->successful()) {
                Log::warning('Ollama API returned non-success', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return 'عذراً، تعذر الاتصال بخدمة التحليل الذكية. الرجاء المحاولة لاحقاً.';
            }

            $body = $response->json();
            if (!isset($body['response'])) {
                Log::warning('Ollama response missing "response" key', ['body' => $body]);
                return 'عذراً، لم نحصل على رد صالح من خدمة التحليل الذكية.';
            }

            return $body['response'];
        } catch (\Exception $e) {
            Log::error('Ollama API request failed', [
                'error' => $e->getMessage(),
                'url'   => $this->url,
            ]);
            return 'عذراً، حدث خطأ تقني أثناء الاتصال بخدمة التحليل الذكية.';
        }
    }
}
