<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Http\Middleware\EnsureAdminIsAuthenticated;
use App\Http\Middleware\SetAdminLocale;
use App\Support\AdminFlags;
use Filament\Actions\Action;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // No ->login(): staff already authenticate through the existing
            // AdminAuthController/TwoFactorChallengeController flow against
            // the same `web` guard. This panel trusts that session instead
            // of shipping its own login page — see the `admin` middleware
            // alias below, the same one guarding that flow.
            ->brandName('Inofarma')
            ->font('Inter')
            ->colors([
                'primary' => Color::hex('#303030'),
                'info' => Color::hex('#005bd3'),
            ])
            // Light by default rather than following the OS/browser
            // preference — staff can still switch to dark from the topbar,
            // this only changes what a first-time visitor sees.
            ->defaultThemeMode(ThemeMode::Light)
            // Livewire SPA navigation: clicking a menu item swaps the page in place
            // instead of reloading it, so the sidebar and its animation stay smooth.
            ->spa()
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('filament.hooks.sidebar-smooth'))
            ->viteTheme('resources/css/filament/admin/theme.css')
            // Language switch in the profile menu: two flags on one row. The
            // active language is outlined; labels stay for screen readers.
            ->userMenuItems([
                Action::make('locale-id')
                    ->label('ID')
                    ->icon(AdminFlags::for('id'))
                    ->url(fn () => route('admin.bahasa', 'id'))
                    ->postToUrl()
                    ->extraAttributes(fn () => [
                        'title' => 'Bahasa Indonesia',
                        'data-lang' => 'id',
                        'data-active' => app()->getLocale() === 'id' ? 'true' : null,
                    ]),
                Action::make('locale-en')
                    ->label('EN')
                    ->icon(AdminFlags::for('en'))
                    ->url(fn () => route('admin.bahasa', 'en'))
                    ->postToUrl()
                    ->extraAttributes(fn () => [
                        'title' => 'English',
                        'data-lang' => 'en',
                        'data-active' => app()->getLocale() === 'en' ? 'true' : null,
                    ]),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            // Replaces the legacy admin topbar bell (NotificationController) —
            // App\Notifications\Admin\LowStock already writes to the standard
            // `database` channel via BranchStockObserver, so this is the only
            // wiring needed; Filament's own bell renders it with mark-read/
            // mark-all-read built in.
            ->databaseNotifications()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetAdminLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            // Same gate as the legacy admin: real `web`-guard check, deactivated
            // -account logout, 30-minute idle timeout, 2FA-pending handling —
            // see EnsureAdminIsAuthenticated. Redirects to the existing
            // admin.masuk login page, not a Filament-generated one.
            ->authMiddleware([
                EnsureAdminIsAuthenticated::class,
            ]);
    }
}
