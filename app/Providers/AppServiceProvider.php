<?php

namespace App\Providers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Policies\LeaveRequestPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);

        // Admin can do everything
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) return true;
        });
    }
}
