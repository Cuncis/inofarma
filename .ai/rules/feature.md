---
paths:
  - 'tests/Feature/**'
---

# Feature

## Avoid asserting flashed session errors across a chain of 3+ requests in one test
With `config/session.php`'s `'serialization' => 'json'`, a `ValidationException`'s flashed `errors` (a `ViewErrorBag`) can come back from the session as a plain array instead of an object in some multi-request test sequences within the same test class — Inertia's shared `errors` prop resolution then fatals with "Call to a member function ... on array" on a *later* request in the same test. Reproduced only in full-class runs, not in isolated single/paired tests — root cause not fully pinned down (suspected JSON session round-tripping interacting with PHPUnit's shared process state), so treat it as an environment quirk, not a app bug.

Workaround: don't chain a request that flashes validation errors together with further requests you then assert on in the same test. Test the "wrong input rejected" case at the model/service level instead (see `tests/Feature/CustomerPhoneOtpTest.php`), and keep HTTP-level tests to happy-path + single-error-then-stop assertions.
