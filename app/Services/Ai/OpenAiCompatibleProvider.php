<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiCompatibleProvider implements AiProviderInterface
{
    private string $provider;

    public function __construct(string $provider)
    {
        $this->provider = $provider;
    }

    public function chat(array $messages, array $options = []): ?string
    {
        $config = config("ai.providers.{$this->provider}");
        $payload = array_merge_recursive([
            'model' => $config['model'],
            'messages' => $messages,
            'stream' => false,
        ], $options);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $config['api_key'],
            'Content-Type' => 'application/json',
        ])->timeout($config['timeout'] ?? 60)
            ->post($config['base_url'], $payload);

        if (!$response->successful()) {
            Log::error("{$this->provider} API error", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        return $response->json('choices.0.message.content');
    }

    public function chatJson(array $messages, array $options = []): ?array
    {
        $jsonOptions = array_merge_recursive($options, [
            'response_format' => ['type' => 'json_object'],
        ]);

        $content = $this->chat($messages, $jsonOptions);

        if ($content === null) {
            return $this->chatFallbackJson($messages, $options);
        }

        $parsed = $this->parseJson($content);
        if ($parsed !== null) {
            return $parsed;
        }

        return $this->chatFallbackJson($messages, $options);
    }

    public function parseJson(?string $content): ?array
    {
        if ($content === null) {
            return null;
        }

        $content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($content));
        $parsed = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning("{$this->provider} JSON parse failed", ['content' => substr($content, 0, 200)]);
            return null;
        }

        return $parsed;
    }

    private function chatFallbackJson(array $messages, array $options): ?array
    {
        $content = $this->chat($messages, $options);

        if ($content === null) {
            return null;
        }

        return $this->parseJson($content);
    }
}
