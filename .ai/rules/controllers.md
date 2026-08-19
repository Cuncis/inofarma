---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Inertia::location() return type: never declare RedirectResponse alone
`Inertia::location($url)` returns `Symfony\Component\HttpFoundation\Response` — a plain 409 with `X-Inertia-Location` for a real Inertia XHR (what every browser request actually is), or a genuine `Illuminate\Http\RedirectResponse` only for a non-Inertia request. A controller method that returns it must not declare `: RedirectResponse` alone — PHP throws a TypeError the moment a real browser hits it, since `Request::inertia()` (checks the `X-Inertia` header) takes the non-RedirectResponse branch. Declare `Symfony\Component\HttpFoundation\Response` (or a union including it) instead — see `CheckoutController::store()` and `PaymentController::create()`.

**Test trap**: a plain `$this->post(...)` in a test does NOT send `X-Inertia: true`, so it only ever exercises the RedirectResponse branch and will pass even when this is broken. To actually exercise the branch a browser takes, use `$this->withHeaders(['X-Inertia' => 'true'])->post(...)` and assert `assertStatus(409)` + `assertHeader('X-Inertia-Location', ...)` — see `CheckoutTest::test_checking_out_online_works_for_a_real_inertia_request` and `PaymentRetryTest::test_reopening_a_payment_session_works_for_a_real_inertia_request`.
