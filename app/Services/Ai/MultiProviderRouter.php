<?php

namespace App\Services\Ai;

use App\Services\Ai\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Log;

class MultiProviderRouter implements AiProviderInterface
{
    private array $providers;
    private array $providerNames;

    public function __construct(array $providers, array $providerNames)
    {
        $this->providers = $providers;
        $this->providerNames = $providerNames;
    }

    public function chat(array $messages, array $options = []): ?string
    {
        $errors = [];

        foreach ($this->providers as $i => $provider) {
            try {
                $result = $provider->chat($messages, $options);
                if ($result !== null) {
                    return $result;
                }
                $errors[] = "{$this->providerNames[$i]}: returned null";
            } catch (\Throwable $e) {
                $errors[] = "{$this->providerNames[$i]}: {$e->getMessage()}";
            }
        }

        Log::error('MultiProviderRouter: all providers failed', ['errors' => $errors]);
        return null;
    }

    public function chatJson(array $messages, array $options = []): ?array
    {
        $errors = [];

        foreach ($this->providers as $i => $provider) {
            try {
                $result = $provider->chatJson($messages, $options);
                if ($result !== null) {
                    return $result;
                }
                $errors[] = "{$this->providerNames[$i]}: returned null";
            } catch (\Throwable $e) {
                $errors[] = "{$this->providerNames[$i]}: {$e->getMessage()}";
            }
        }

        Log::error('MultiProviderRouter: all providers failed for chatJson', ['errors' => $errors]);
        return null;
    }

    public function parseJson(?string $content): ?array
    {
        if (empty($this->providers)) {
            return null;
        }
        return $this->providers[0]->parseJson($content);
    }
}
