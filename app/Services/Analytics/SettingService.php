<?php

namespace App\Services\Analytics;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService {
    public static function get($key, $default = null) {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            return Setting::where('key', $key)->value('value') ?? $default;
        });
    }
}
