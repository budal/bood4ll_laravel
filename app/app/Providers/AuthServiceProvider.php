<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
    ];

    public function boot(): void
    {
        // Gate::guessPolicyNamesUsing(function (string $modelClass) {
        //     dd($modelClass);
        // });

        Gate::before(function (User $user, $ability) {
            if ($user->isSuperAdmin($user)) {
                return true;
            }

            if ($user->abilities()->pluck('ability')->contains($ability)) {
                return true;
            }
        });
    }
}
