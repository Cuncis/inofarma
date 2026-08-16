<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        // Super Admin bypasses every permission check — spatie/laravel-permission
        // registers a Gate for each permission name, so `$user->can('Produk:Ubah')`
        // works directly without a Policy class per model. See .ai/rules/support.md.
        Gate::before(fn ($user, string $ability) => $user instanceof User && $user->hasRole('Super Admin') ? true : null);
    }
}
