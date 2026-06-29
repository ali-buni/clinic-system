<?php

namespace App\Providers;

use App\Services\Analytics\SettingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingService::class, function ($app) {
            return new SettingService($app->make('cache.store'));
        });
    }

    public function boot(): void
    {
        //
    }
}
