---
paths:
  - app/Http/Middleware/EnsureAdminIsAuthenticated.php
---

# Middleware

## Admin area is guarded by the real `web` guard (Fase 3.1)
Every `/admin/*` route except `masuk`, `masuk.store`, `lupa-sandi(.store)`, `atur-ulang-sandi(.store)` and `dua-faktor(.store)` sits behind the `admin` middleware alias (`EnsureAdminIsAuthenticated`), which checks `Auth::guard('web')->check()`, that the account is `is_active`, and a 30-minute session idle timeout (`admin_last_activity` in session). `AdminAuthController` uses real `Auth::attempt`-equivalent credential checks (`Admin\LoginRequest::attemptCredentials()`), with a 5-attempts-per-minute-per-IP throttle.

A staff member with 2FA confirmed (`User::hasEnabledTwoFactor()`) does not get logged in by `AdminAuthController::login()` — it stops short of establishing the session and redirects to `TwoFactorChallengeController`, which is the only place that actually calls `establishSession()`. Don't add a session-establishing call anywhere else in the login path.

Tests hitting guarded routes must `use SignsInAsAdmin` and sign in during `setUp()` (real credentials against a seeded/factory `User`) — that trait posts real credentials against the real guard. A CRUD test that suddenly 302s to `/admin/masuk` is a missing sign-in or a missing permission grant on the test user's role, not a broken route.

Route groups also carry `permission:{Module}:{Ability}` middleware (spatie/laravel-permission) matching `App\Support\PermissionCatalog`. Kategori and Penjual (Seller) are deliberately NOT permission-gated yet (no catalog module was ever defined for them) — only the generic `admin` session check applies, same as before Fase 3.2. Extending that is a small, mechanical follow-up, not a design question.

The storefront's `customer` guard (`EnsureCustomerIsAuthenticated`) is completely separate — see the auth-guards rule in this directory's models section. Do not merge the two.
