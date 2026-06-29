<?php

namespace App\Providers;

use App\Services\Analytics\SettingService;
use App\Services\Ai\Contracts\AiProviderInterface;
use App\Services\Ai\MultiProviderRouter;
use App\Services\Ai\OllamaClient;
use App\Services\Ai\OpenAiCompatibleProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingService::class, function ($app) {
            return new SettingService($app->make('cache.store'));
        });

        $this->app->bind(AiProviderInterface::class, function () {
            $providerNames = explode(',', config('ai.default', 'ollama'));

            if (count($providerNames) > 1) {
                return $this->buildMultiProvider($providerNames);
            }

            return $this->buildSingleProvider(trim($providerNames[0]));
        });
    }

    private function buildSingleProvider(string $name): AiProviderInterface
    {
        $config = config("ai.providers.{$name}");
        if (!$config) {
            throw new \RuntimeException("AI provider '{$name}' is not configured in config/ai.php");
        }

        return match ($config['driver'] ?? 'ollama') {
            'openai' => new OpenAiCompatibleProvider($name),
            default => new OllamaClient,
        };
    }

    private function buildMultiProvider(array $providerNames): MultiProviderRouter
    {
        $providers = [];
        $names = [];

        foreach ($providerNames as $name) {
            $name = trim($name);
            try {
                $providers[] = $this->buildSingleProvider($name);
                $names[] = $name;
            } catch (\RuntimeException $e) {
                // skip misconfigured providers in chain
                continue;
            }
        }

        if (empty($providers)) {
            throw new \RuntimeException('No AI providers could be initialized from the chain: ' . implode(',', $providerNames));
        }

        return new MultiProviderRouter($providers, $names);
    }

    public function boot(): void
    {
        //
    }
}
