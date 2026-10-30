<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Sign-in tokens stop working after TOKEN_EXPIRY_HOURS (.env); the user then signs in again.
        Passport::personalAccessTokensExpireIn(now()->addHours(config('auth.token_expiry_hours')));
    }
}
