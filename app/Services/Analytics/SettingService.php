<?php

namespace App\Services\Analytics;

use App\Models\Setting;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Support\Facades\Log;

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
        $setting = Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        $this->cache->forget("setting_{$key}");

        activity()
            ->performedOn($setting)
            ->withProperties(['key' => $key, 'was_recently_created' => $setting->wasRecentlyCreated])
            ->event($setting->wasRecentlyCreated ? 'created' : 'updated')
            ->log('setting updated');

        Log::channel('structured')->info('setting updated', [
            'setting_id' => $setting->id, 'key' => $key, 'was_recently_created' => $setting->wasRecentlyCreated,
        ]);
    }

    public function forget(string $key): void
    {
        $this->cache->forget("setting_{$key}");
    }
}
