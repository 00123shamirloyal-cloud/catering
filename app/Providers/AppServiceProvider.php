<?php

namespace App\Providers;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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
        Gate::define('manage-website', fn (User $user): bool => $user->canManageWebsite());
        Gate::define('publish-website', fn (User $user): bool => $user->canPublishWebsite());
        Gate::define('manage-finance', fn (User $user): bool => $user->canManageFinance());
        Gate::define('manage-payments', fn (User $user): bool => $user->canManagePayments());
        Gate::define('manage-events', fn (User $user): bool => $user->canManageEvents());
        Gate::define('manage-appointments', fn (User $user): bool => $user->canManageAppointments());
        Gate::define('manage-staff', fn (User $user): bool => $user->role === 'owner' && $user->is_active);
        Gate::define('view-reports', fn (User $user): bool => in_array($user->role, ['owner', 'manager', 'finance'], true) && $user->is_active);
        Gate::define('view-crm', fn (User $user): bool => $user->canViewCrm());
        Gate::define('manage-customers', fn (User $user): bool => $user->canManageCustomers() && $user->is_active);

        View::composer('layouts.public', function ($view): void {
            $view->with('siteSettings', SiteSetting::values());
        });
    }
}
