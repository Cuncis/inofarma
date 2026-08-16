---
paths:
  - 'app/Http/Controllers/Api/**,app/Support/LocationPreference.php,routes/api.php'
---

# Api Support

## routes/api.php routes need ->middleware('web') to read the session
bootstrap/app.php registers routes/api.php via withRouting(api: ...), which Laravel puts under the stock `api` middleware group — stateless, no session, no cookies. LocationPreference (used by the branch locator endpoints) reads the shopper's saved location from session, so those routes explicitly add ->middleware('web') to the group in routes/api.php. This is safe because they're GET-only — VerifyCsrfToken (part of the web group) only guards state-changing verbs.

If you add a new api.php route that needs the session, remember the same middleware('web') addition or it will throw "Session store not set on request." If it doesn't need session, leave the default api group alone.

`app.php`'s shouldRenderJsonWhen already treats `api/*` as JSON-error paths regardless of middleware, so error responses are consistent either way.
