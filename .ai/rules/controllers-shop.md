---
paths:
  - 'resources/js/Pages/Shop/OrderDetail.jsx,resources/js/Pages/Shop/TrackOrder.jsx,app/Http/Controllers/Shop/OrderController.php'
---

# Controllers Shop

## Lacak Pesanan vs Detail Pesanan — tracking timeline has no actions
Two separate shop pages for one order, on purpose: `ui.pesanan.show` (`Shop/OrderDetail.jsx`) is items, total, and the Bayar/Batalkan actions — the primary landing page (Order History rows, DOKU's `callback_url*`, the checkout DOKU-failure fallback, `PaymentReceived`'s "Lihat Pesanan"). `ui.track-order` (`Shop/TrackOrder.jsx`) is read-only: the shipment/pickup timeline only, no action buttons, reached via a "Lacak Pesanan" link from the detail page.

`Shop\OrderController::show()` renders the detail page, `::track()` renders the timeline — both call the same `ShopOrderPresenter::toArray()` (unchanged; still exposes `canPay`/`isCancellable`/`items`/`total` alongside `steps`/`shipment`/`pickup`, just rendered by different pages now).

Deciding which existing links point where followed each one's own literal label: anything already saying "Lacak Pesanan" (`OrderSuccessful.jsx`, `OrderShipped`, `OrderConfirmed` notifications) or pickup-code-specific (`OrderReadyForPickup`) stayed on `ui.track-order`; anything about viewing/paying for the order itself moved to `ui.pesanan.show`.
