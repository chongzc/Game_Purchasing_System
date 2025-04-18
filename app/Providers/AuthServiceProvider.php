<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates for different user roles
        Gate::define('is-admin', function (User $user) {
            return $user->u_role === 'admin';
        });

        Gate::define('is-developer', function (User $user) {
            return $user->u_role === 'developer';
        });

        Gate::define('is-user', function (User $user) {
            return $user->u_role === 'user';
        });
    }
} 
