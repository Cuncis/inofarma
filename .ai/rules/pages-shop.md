---
paths:
  - 'app/Http/Controllers/Shop/GuestCheckoutController.php,app/Http/Controllers/Shop/CheckoutController.php,app/Support/Cart/**,resources/js/Pages/Shop/GuestCheckout.jsx,resources/js/Pages/Shop/Cart.jsx'
---

# Pages Shop

## Guest checkout ships: it silently creates a real Customer account, no schema change
Fase 0's "boleh checkout sebagai tamu?" is decided: yes. `GuestCheckoutController` (public routes `ui.checkout.tamu` / `.store`, and `ui.wilayah` is now public too since the address cascade needs it pre-login) collects name/phone/email/consent + a full address from a guest in one form (`Shop/GuestCheckout.jsx`), then on submit: creates a real `Customer` row (random unusable password — `ForgotPassword` is how they'd set a real one later), logs them in via `Auth::guard('customer')->login()`, merges their session cart in (`CartManager::mergeGuestIntoCustomer()`), creates+attaches a default `CustomerAddress` from the submitted fields, and redirects to the normal `ui.checkout` — which now Just Works, unchanged, because the shopper is a real signed-in customer by the time they reach it.

This means `orders.customer_id` stayed NOT NULL and `CheckoutController`/`placeOrder()` needed zero changes — every downstream thing (DOKU payment, order tracking, order history, coupon one-per-customer dedup, admin's customer view) already assumed a real `Customer` and still does. If the email a guest enters is already registered, they get a validation error pointing them to sign in instead (existing login flow already merges the guest cart, so nothing is lost).

`AuthController::CONSENT_VERSION` is `public` (was `private`) so `GuestCheckoutController` can stamp the same PDP consent version — keep them in sync if it's ever bumped.

`Shop/AddressFields.jsx` is the shared Provinsi/Kota/Kecamatan/Kelurahan/Kode Pos cascade + "Gunakan lokasi saya" block, extracted so both `AddNewAddress.jsx` (signed-in) and `GuestCheckout.jsx` (not yet signed in) use one implementation.
