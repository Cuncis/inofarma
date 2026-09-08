---
paths:
  - 'app/Support/Shipping/**,app/Filament/Resources/Orders/**'
---

# Shipping Http Controllers Admin

## Shipment "Cek Status Kirim" — manual Biteship reconcile, mirrors the DOKU payment one
`ShipmentService::reconcile(Shipment $shipment)` manually pulls Biteship's own tracking record (`BiteshipClient::track()`, `GET /trackings/{tracking_id}`) and applies it through the same `applyStatus()` private helper the webhook path (`applyWebhookEvent()`) uses — same reasoning as `DokuPaymentService::reconcile()` for payments: the webhook can be late, lost, or (in local development) simply unreachable since Biteship can't call back to `localhost`.

Unlike the webhook (which must look up the `Shipment` from a payload's `order_id`), `reconcile()` is handed the `Shipment` directly, so it skips straight to `applyStatus()`. Requires `shipment.tracking_id` to be set (i.e. already booked via `ship()`/`bookForOrder()`) — throws otherwise, since there's nothing to track yet.

Wired up as a header action (`checkShipmentStatus`) on `App\Filament\Resources\Orders\Pages\ViewOrder`, visible only when `$record->shipment?->is_booked` — same condition `Admin/OrderDetail.jsx`'s "Cek Status Kirim" button used to check via `order.shipment.isBooked`. `ship`/`markReady` on the same page are gated by `Pesanan:Proses`, matching the permission the legacy route required.

Note: `track()`'s real response envelope from Biteship's status field/`courier.waybill_id` nesting was inferred by analogy with `createOrder()`'s documented response shape, not independently verified against a live call — `applyStatus()` degrades safely (keeps existing values via `??`) if a key turns out to be named differently, so a shape mismatch produces a no-op, not wrong data.
