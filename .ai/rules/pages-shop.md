---
paths:
  - 'app/Http/Controllers/Shop/CheckoutController.php,app/Support/Cart/**,resources/js/Pages/Shop/Checkout.tsx,resources/js/Pages/Shop/Cart.tsx'
---

# Pages Shop

## Guest checkout ships: it silently creates a real Customer account, no schema change
Fase 0's "boleh checkout sebagai tamu?" is decided: yes, and there is ONE checkout page for everyone (`Shop/Checkout.tsx`, `ui.checkout`, public along with `ui.checkout.ongkir` and `ui.wilayah`). `/checkout/tamu` only redirects there; the old GuestCheckoutController and page are gone. A guest fills email/name/phone/consent, the address (AddressFields cascade), delivery or pickup, and pays, all in one form and one POST. A shopper without a saved address (`cart.address` null) types it inline; one with a saved address sees the saved-address card.

`CheckoutController::store()` validates everything first (address, courier re-quote, pickup stock) and only then, for a guest, `registerGuest()` creates the real `Customer` (random unusable password), logs in, merges the session cart and saves the typed address as default, so a refused order never leaves a stray account. Email already registered is a validation error pointing to sign in. Address is required only for `antar`; for `ambil` it is optional (it only feeds the distance).

An unsaved address is a `DraftAddress` (app/Support/Cart): no coordinates means a postal-code geocode (Nominatim, cached) so Biteship can quote. `GET /checkout/ongkir` accepts `postalCode`/`latitude`/`longitude` for an address not saved yet. Pickup distance: once the address has a 5-digit postal code or a map pin, Checkout.tsx POSTs `/lokasi` then reloads only `pickupBranches`; a saved address's coordinates are the fallback in `CheckoutController::pickupBranches()`.

`AuthController::CONSENT_VERSION` is `public` so `CheckoutController` can stamp the same PDP consent version for guests, keep them in sync if it's ever bumped.

`Shop/AddressFields.tsx` is the shared Provinsi/Kota/Kecamatan/Kelurahan/Kode Pos cascade + "Gunakan lokasi saya" block, extracted so both `AddNewAddress.tsx` (signed-in) and `Checkout.tsx` (not yet signed in) use one implementation.
