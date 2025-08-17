<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // ...
    ];

    public function boot(): void
    {
        // Allow ferryowner@test.com to manage ferry schedules
        Gate::define('manage-ferry', function ($user) {
            return $user && strcasecmp($user->email, 'ferryowner@test.com') === 0;
        });

        // Allow hotelowner@test.com to manage ferry schedules
        Gate::define('manage-rooms', fn($user) =>
            $user && strcasecmp($user->email, 'hotelowner@test.com') === 0
        );

        // Allow eventowner@test.com to manage events
        Gate::define('manage-events', fn($user) =>
            $user && strcasecmp($user->email, 'eventowner@test.com') === 0
        );

        // Allows the admin@test.com to manage everything
        Gate::define('admin', fn($user) =>
            $user && strcasecmp($user->email, 'admin@test.com') === 0
        );
    }
}