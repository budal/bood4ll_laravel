<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::guessPolicyNamesUsing(function (string $modelClass) {
            // dd($modelClass);
            // Return the name of the policy class for the given model...
        });

        Gate::before(function (User $user, $ability) {
            if ($user->isSuperAdmin($user)) {
                return true;
            }

            if ($user->abilities()->contains($ability)) {
                return true;
            }

            return false;
        });
    }
}
