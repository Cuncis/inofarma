<?php

namespace App\Providers;

use App\Models\BranchStock;
use App\Models\ChatMessage;
use App\Models\Order;
use App\Models\User;
use App\Observers\BranchStockObserver;
use App\Observers\ChatMessageObserver;
use App\Observers\OrderObserver;
use Filament\Forms\Components\FileUpload;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Upload boxes always read "Drag & Drop your files or Browse", never the
        // Indonesian rendering of those technical terms.
        $this->app->bind(FileUpload::class, \App\Filament\Forms\FileUpload::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Chat is open to anonymous visitors, so each step is limited per IP:
        // a few new conversations an hour, a steady stream of messages, and
        // enough polling for a couple of open tabs.
        RateLimiter::for('chat-start', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
        RateLimiter::for('chat-send', fn (Request $request) => Limit::perMinute(15)->by($request->ip()));
        RateLimiter::for('chat-poll', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));

        // Super Admin bypasses every permission check — spatie/laravel-permission
        // registers a Gate for each permission name, so `$user->can('Produk:Ubah')`
        // works directly without a Policy class per model. See .ai/rules/support.md.
        Gate::before(fn ($user, string $ability) => $user instanceof User && $user->hasRole('Super Admin') ? true : null);

        // Fase 8: every order status/payment transition and every low-stock
        // crossing notifies through here — see the observers themselves.
        // Fase 8: every order status/payment transition and every low-stock
        // crossing notifies through here — see the observers themselves.
        Order::observe(OrderObserver::class);
        BranchStock::observe(BranchStockObserver::class);
        ChatMessage::observe(ChatMessageObserver::class);
    }
}
