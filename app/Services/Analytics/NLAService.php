<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Http;

class NLAService
{
    public function askAnalytics(string $question, string $context)
    {
        $model = 'llama3.2';
        $url   = 'http://127.0.0.1:11434/api/generate';

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
            $response = Http::timeout(120)->post($url, [
                'model'  => $model,
                'prompt' => $systemPrompt . "\n\nUSER QUESTION: " . $question,
                'stream' => false,
            ]);

            return $response->successful()
                ? $response->json()['response']
                : "Error connecting to AI";
        } catch (\Exception $e) {
            return "Technical error: " . $e->getMessage();
        }
    }
}
