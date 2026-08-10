<?php

namespace App\Services\Analytics;

use App\Constants\Prompt;
use App\Services\Ai\Contracts\AiProviderInterface;
use App\Support\TextSanitizer;
use Illuminate\Support\Facades\Log;

class NLAService
{
    public function __construct(
        private readonly AiProviderInterface $ai,
    ) {}

    public function askAnalytics(string $question, string $context): string
    {
        $systemPrompt = Prompt::NLA.$context;

        try {
            $response = $this->ai->chat(
                messages: [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => "<question>\n{$question}\n</question>"],
                ],
                options: [
                    'options' => [
                        'num_ctx' => 3072,
                        'temperature' => 0.4,
                    ],
                ]
            );

            if ($response === null) {
                Log::warning('NLAService: AI returned null');

                return 'عذراً، تعذر الاتصال بخدمة التحليل الذكية. الرجاء المحاولة لاحقاً.';
            }

            return TextSanitizer::html($response);
        } catch (\Throwable $e) {
            Log::error('NLAService request failed', ['error' => $e->getMessage()]);

            return 'عذراً، حدث خطأ تقني أثناء الاتصال بخدمة التحليل الذكية.';
        }
    }
}
