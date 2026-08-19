---
paths:
  - 'app/Support/Shipping/**,app/Http/Controllers/Admin/OrderController.php'
---

# Shipping Http Controllers Admin

## Shipment "Cek Status Kirim" — manual Biteship reconcile, mirrors the DOKU payment one
`ShipmentService::reconcile(Shipment $shipment)` manually pulls Biteship's own tracking record (`BiteshipClient::track()`, `GET /trackings/{tracking_id}`) and applies it through the same `applyStatus()` private helper the webhook path (`applyWebhookEvent()`) uses — same reasoning as `DokuPaymentService::reconcile()` for payments: the webhook can be late, lost, or (in local development) simply unreachable since Biteship can't call back to `localhost`.

Unlike the webhook (which must look up the `Shipment` from a payload's `order_id`), `reconcile()` is handed the `Shipment` directly, so it skips straight to `applyStatus()`. Requires `shipment.tracking_id` to be set (i.e. already booked via `ship()`/`bookForOrder()`) — throws otherwise, since there's nothing to track yet.

Wired up as `Admin\OrderController::checkShipmentStatus()`, route `POST /admin/pesanan/{order}/cek-status-kirim` (permission `Pesanan:Proses`), "Cek Status Kirim" button on `Admin/OrderDetail.jsx` shown only when `order.shipment.isBooked`.

Note: `track()`'s real response envelope from Biteship's status field/`courier.waybill_id` nesting was inferred by analogy with `createOrder()`'s documented response shape, not independently verified against a live call — `applyStatus()` degrades safely (keeps existing values via `??`) if a key turns out to be named differently, so a shape mismatch produces a no-op, not wrong data.
