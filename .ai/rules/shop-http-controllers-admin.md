---
paths:
  - 'app/Http/Controllers/Shop/PrivacyController.php,app/Http/Controllers/Shop/AuthController.php,app/Support/Money.php,resources/js/Components/Shop/useLocationConsent.ts,resources/js/Components/Shop/legalDocuments.ts,resources/js/Components/Shop/LegalModal.tsx,resources/js/Pages/Shop/RefundPolicy.tsx,resources/js/Pages/Shop/ShippingInfo.tsx,app/Filament/Resources/Products/**'
---

# Shop Http Controllers Admin

## Compliance conventions (Fase 9): consent, PDP self-service, audit trail
Two separate consent gates exist, never merge them: account consent (`Customer.consent_at`/`consent_version`, ticked at registration via `Shop/SignUp.tsx`, enforced by `AuthController::register()`'s `'consent' => ['accepted']` rule) vs. location consent (`useLocationConsent()`, a localStorage flag shared by every `navigator.geolocation` call site — currently `Shop/OurBranches.tsx` and `Shop/AddNewAddress.tsx`). Any new screen that calls `navigator.geolocation` must gate it behind `useLocationConsent()` too, not just wire the browser's own permission prompt.

PDP self-service lives at `/privasi-saya` (`Shop\PrivacyController`) — `export()` streams a JSON download, `destroyAccount()` (password-confirmed) clears addresses/cart, scrambles name/email/phone, then soft-deletes. Order history is deliberately preserved (bookkeeping obligation) — never hard-delete or anonymize `orders` rows on account deletion.

Syarat & Ketentuan and Kebijakan Privasi are popups (`Components/Shop/LegalModal.tsx`, which fetches the text from `/konten/dokumen/{terms|privacy}` when opened; the old `/syarat-ketentuan` and `/kebijakan-privasi` pages are gone). Together with `Shop/RefundPolicy.tsx` and `ShippingInfo.tsx` they are real compliance content, not prototype placeholders — when app mechanics change (payment provider, courier, windows, refund process), update the matching section instead of leaving it stale. Their text is edited in the admin under Konten Website (`App\Support\DocumentPageContent`, shipped text in `DocumentPageDefaults`), so update it there, or in `DocumentPageDefaults` for a fresh install. The "Tentang Kami" page was removed: every "Tentang Kami" link now goes to the external site http://info.inofarma.com/

Audit logging convention: `App\Filament\Resources\Products\Pages\{CreateProduct,EditProduct}` log `produk_ditambahkan`/`produk_diubah`/`produk_dihapus` via `AuditLogger::log()`, capturing before/after only for the pharmacy-relevant fields (price, drug_class, nie_bpom, composition, dosage, side_effects, warning, manufacturer, storage, status — not cosmetic fields like blurb; see `EditProduct::AUDITED_FIELDS`). Every order creation logs `pesanan_dibuat` via `App\Observers\OrderObserver::created()` (Fase 8's observer, not a new call site), with `branchId` passed explicitly since the acting user has no branch for a customer checkout. Follow this pattern — audited-fields allowlist, branch passed explicitly — for any future CRUD that needs an audit trail rather than logging every column.

Money in a JS/PHP boundary needing plain-text Rupiah (legal page bodies, email bodies) uses `App\Support\Money::rupiah()` server-side or `Components/{Shop,Admin}/data.ts`'s `money()` client-side — never re-implement `number_format` inline.
