---
paths:
  - 'app/Support/Shipping/**,app/Support/Pickup/**,app/Http/Controllers/Webhooks/**,app/Http/Controllers/Shop/CheckoutController.php,app/Http/Controllers/Shop/ShippingController.php,app/Http/Controllers/Admin/OrderController.php,app/Http/Controllers/Admin/PickupController.php,app/Models/Shipment.php'
---

# Admin Models

## Biteship integration (Fase 7): quote at checkout, book from admin, no webhook signature
Biteship client is hand-rolled (`App\Support\Shipping\Biteship\BiteshipClient`), same reasoning as DOKU's client — no well-maintained official SDK, and the surface needed is small (couriers, rates, orders, trackings).

Split checkout-time quote from admin-time booking, deliberately: `CheckoutController::store()` calls `POST /v1/rates/couriers` (via `ShippingQuoteService`) and only *records* the chosen courier + price on a `Shipment` row (`biteship_order_id` stays null) — it never calls `POST /v1/orders`. An admin books the real waybill later from `Admin\OrderController::ship()` (`ShipmentService::bookForOrder()`), matching ROADMAP.md 7.1's "buat label dan resi dari admin cabang". Never make checkout itself call `POST /v1/orders`.

Never hardcode a courier company whitelist — `BiteshipClient::rates()` first calls `GET /v1/couriers` to discover whatever's actually active on the merchant's Biteship account and passes that as the `couriers` param. A merchant activating/deactivating a courier in their dashboard must not require a code change here.

Biteship's webhook (`Webhooks\BiteshipWebhookController`, `POST /biteship/notifikasi`) has **no signature scheme** — confirmed against their own docs. The `?token=` query string is a shared secret this app generates and the merchant pastes into the Notification URL in the Biteship dashboard; it is the entire auth boundary for that route (also CSRF-exempted in `bootstrap/app.php`, same as `doku/notifikasi`). Don't assume a `Signature`/`X-*-Signature` header exists for Biteship the way it does for DOKU.

`orders.pickup_code` is only ever issued by `App\Support\Pickup\PickupCodeService::issue()` when an admin marks a pickup order `siap diambil` — never at checkout, since the code means nothing until the item is staged. QR rendering reuses `bacon/bacon-qr-code` (already a direct dependency for 2FA) — never add a QR package or call an external QR image service; this app self-hosts everything (see Fase 4's illustration self-hosting).

Both expiry sweeps (`pesanan:kadaluwarsakan` for payment, `pesanan:kadaluwarsakan-pengambilan` for pickup) funnel through the same `App\Support\OrderCancellation::apply()` to return stock — never write a second stock-return path.
