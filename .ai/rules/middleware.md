---
paths:
  - app/Http/Middleware/EnsureAdminIsAuthenticated.php
---

# Middleware

## Admin area is guarded by a session stand-in, not a real auth guard
Every `/admin/*` route except `masuk`, `masuk.store` and `lupa-sandi` sits behind the `admin` middleware alias (`EnsureAdminIsAuthenticated`). It only checks the session carries `admin_user`; `AdminAuthController@login` accepts ANY email and password and derives a display name from the email local part. There is no user table and no password check yet.

To make it real: replace the middleware body with Laravel's `auth` guard and `login()` with `Auth::attempt()`. Nothing else should need touching — the routes, the login screen and the tests already exercise the real path.

Login records the blocked URL in `admin_intended` and returns you there afterwards; don't drop that when reworking it.

Tests hitting guarded routes must `use SignsInAsAdmin` and sign in during `setUp()` — that trait posts real credentials rather than writing the session key, so it keeps working once a real guard lands. A CRUD test that suddenly 302s to `/admin/masuk` is a missing sign-in, not a broken route.

The storefront's separate `shop_user` session prototype is unrelated and unguarded — do not merge the two.
