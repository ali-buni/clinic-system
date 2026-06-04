<?php

namespace App\Providers;

use App\Models\Secretary;
use App\Policies\ViewSecretaryPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // protected array $policies = [
    //     Secretary::class => ViewSecretaryPolicy::class,
    // ];
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Gate::policy(Secretary::class, ViewSecretaryPolicy::class);

        // Define the 'viewSecretary' gate for view secretary
        Gate::define('viewSecretary', function ($user, $user_room_id, $secretary_room_id) {
            return Auth::user()->hasRole('owner')
                ||
                ($user->can("view room {$user_room_id}") && $secretary_room_id == $user_room_id);
        });
    }
}
