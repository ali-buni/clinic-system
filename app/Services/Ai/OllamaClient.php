<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaClient
{
    public function chat(array $messages, array $options = []): ?string
    {
        $response = Http::timeout(config('ai.timeout', 180))->post(
            config('ai.url') . '/api/chat',
            array_merge_recursive([
                'model' => config('ai.model', 'qwen2.5:1.5b'),
                'messages' => $messages,
                'stream' => false,
                'options' => [
                    'num_thread' => 2,
                ],
                'keep_alive' => '30m',
            ], $options)
        );

        if (!$response->successful()) {
            Log::error('Ollama API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        return $response->json('message.content');
    }

    public function chatJson(array $messages, array $options = []): ?array
    {
        $optionsWithFormat = array_merge_recursive($options, [
            'format' => 'json',
        ]);

        $content = $this->chat($messages, $optionsWithFormat);

        if ($content === null) {
            return null;
        }

        return $this->parseJson($content);
    }

    public function parseJson(?string $content): ?array
    {
        if ($content === null) {
            return null;
        }

        $content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($content));
        $parsed = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Ollama JSON parse failed', ['content' => substr($content, 0, 200)]);
            return null;
        }

        return $parsed;
    }
}
