<?php

namespace App\Services\Analytics;

use App\Models\Setting;
use Illuminate\Contracts\Cache\Repository as CacheContract;

class SettingService
{
    public function __construct(
        private readonly CacheContract $cache,
        private readonly int $ttl = 3600,
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->remember(
            "setting_{$key}",
            $this->ttl,
            fn() => Setting::where('key', $key)->value('value') ?? $default,
        );
    }

    public function set(string $key, string $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        $this->cache->forget("setting_{$key}");
    }

    public function forget(string $key): void
    {
        $this->cache->forget("setting_{$key}");
    }
}
