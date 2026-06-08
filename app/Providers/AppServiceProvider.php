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
        Gate::define('viewSecretary', function ($user, $user_room_id, $secretary_room_ids) {
            // if (Auth::user()->hasRole('owner')) {
            //     return true;
            // }

            // if (empty($user_room_id) || empty($secretary_room_ids)) {
            //     return false;
            // }

            // // secretary_room_ids can be array; check intersection
            // $userRooms = (array) $user_room_id; // convert int to array
            // $hasAccess = !empty(array_intersect($userRooms, $secretary_room_ids));
            // if ($hasAccess) {
            //     return $user->can("view room {$user_room_id}");
            // }

            // return false;
            return true;
        });
    }
}
