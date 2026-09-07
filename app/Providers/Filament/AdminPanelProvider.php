<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureAdminIsAuthenticated;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
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
            // TEMP during the Filament migration (see .ai/rules) — parked off
            // the real /admin path so the existing Inertia admin keeps
            // working undisturbed until every module is ported. Flip both
            // back to 'admin' at final cutover.
            ->id('beta')
            ->path('admin/beta')
            // No ->login(): staff already authenticate through the existing
            // AdminAuthController/TwoFactorChallengeController flow against
            // the same `web` guard. This panel trusts that session instead
            // of shipping its own login page — see the `admin` middleware
            // alias below, the same one guarding the legacy Inertia routes.
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
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
