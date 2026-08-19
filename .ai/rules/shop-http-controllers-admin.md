---
paths:
  - 'app/Http/Controllers/Shop/PrivacyController.php,app/Http/Controllers/Shop/AuthController.php,app/Support/Money.php,resources/js/Components/Shop/useLocationConsent.js,resources/js/Pages/Shop/Terms.jsx,resources/js/Pages/Shop/PrivacyPolicy.jsx,resources/js/Pages/Shop/RefundPolicy.jsx,resources/js/Pages/Shop/AboutUs.jsx,resources/js/Pages/Shop/ShippingInfo.jsx,app/Http/Controllers/Admin/ProductController.php'
---

# Shop Http Controllers Admin

## Compliance conventions (Fase 9): consent, PDP self-service, audit trail
Two separate consent gates exist, never merge them: account consent (`Customer.consent_at`/`consent_version`, ticked at registration via `Shop/SignUp.jsx`, enforced by `AuthController::register()`'s `'consent' => ['accepted']` rule) vs. location consent (`useLocationConsent()`, a localStorage flag shared by every `navigator.geolocation` call site — currently `Shop/OurBranches.jsx` and `Shop/AddNewAddress.jsx`). Any new screen that calls `navigator.geolocation` must gate it behind `useLocationConsent()` too, not just wire the browser's own permission prompt.

PDP self-service lives at `/ui/privasi-saya` (`Shop\PrivacyController`) — `export()` streams a JSON download, `destroyAccount()` (password-confirmed) clears addresses/cart, scrambles name/email/phone, then soft-deletes. Order history is deliberately preserved (bookkeeping obligation) — never hard-delete or anonymize `orders` rows on account deletion.

`Shop/Terms.jsx`, `PrivacyPolicy.jsx`, `RefundPolicy.jsx`, `AboutUs.jsx`, `ShippingInfo.jsx` are real compliance content, not prototype placeholders — when app mechanics change (payment provider, courier, windows, refund process), update the matching section instead of leaving it stale. `AboutUs.jsx`'s legal-entity fields (PT name, NIB, NPWP) are intentional bracketed placeholders, not fabricated numbers — never fill these with invented-looking values; leave the placeholder until the real business identity exists (ROADMAP.md 0.1).

Audit logging convention: `Admin\ProductController` logs `produk_ditambahkan`/`produk_diubah`/`produk_dihapus` via `AuditLogger::log()`, capturing before/after only for `ProductController::AUDITED_FIELDS` (the pharmacy-relevant ones — price, drug_class, nie_bpom, composition, dosage, side_effects, warning, manufacturer, storage, status — not cosmetic fields like blurb). Every order creation logs `pesanan_dibuat` via `App\Observers\OrderObserver::created()` (Fase 8's observer, not a new call site), with `branchId` passed explicitly since the acting user has no branch for a customer checkout. Follow this pattern — audited-fields allowlist, branch passed explicitly — for any future CRUD that needs an audit trail rather than logging every column.

Money in a JS/PHP boundary needing plain-text Rupiah (legal page bodies, email bodies) uses `App\Support\Money::rupiah()` server-side or `Components/{Shop,Admin}/data.js`'s `money()` client-side — never re-implement `number_format` inline.
