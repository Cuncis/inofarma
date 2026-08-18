---
paths:
  - 'app/Support/Payments/**,app/Http/Controllers/Webhooks/**,app/Http/Controllers/Shop/CheckoutController.php,app/Http/Controllers/Shop/PaymentController.php,app/Models/Payment.php,app/Console/Commands/ExpireUnpaidOrders.php,app/Support/OrderCancellation.php'
---

# Commands Support

## DOKU payment integration — webhook is truth, hand-rolled client, cash-at-pickup only for ambil
Fase 6, hand-rolled against DOKU's real API docs (developers.doku.com) rather than an unofficial Packagist package — see `DokuClient`'s docblock.

**Webhook is the only source of truth for payment status**, never the browser callback. `callback_url`/`callback_url_cancel`/`callback_url_result` (all pointed at `ui.track-order`) only decide where the customer's browser lands — they write nothing. Only `POST /doku/notifikasi` (`DokuWebhookController`) writes `Payment`/`Order` state, and only after `DokuSignature::verify()` passes. That route is CSRF-exempted in `bootstrap/app.php` and its path is duplicated in `services.doku.notification_path` — the two must always match, since the path is itself signed.

**`payments` is a gateway-attempt log, `orders.payment_status` stays authoritative.** Every other screen (Faktur, admin order list) reads `orders.payment_status`/`payment_method`, never `Payment` directly. An order can have >1 `Payment` row (retries after an expired/failed session) — invoice numbers are `{order.number}` then `{order.number}-R2`, `-R3`, ... (`DokuPaymentService::nextInvoiceNumber()`).

**Stock consumption timing didn't change for Fase 6** — still immediate at order creation via `StockAllocator::consume()` (Fase 5), not held in `reserved_quantity`. `OrderCancellation::apply()` (extracted from Fase 5's cancel flow) is the one place that returns stock, called by: customer cancel, `pesanan:kadaluwarsakan` (scheduled every 5 min, 24h window), and DOKU's own EXPIRED notification.

**"online" vs "Tunai" is the only payment_method choice at checkout** (`CheckoutController`) — not the old Transfer Bank/GoPay/OVO/DANA list (that stayed as `AdminOptions::paymentMethods()` for the *admin's own* manual order form only, unrelated to shop checkout now). DOKU's hosted Checkout page is where a shopper actually picks VA/e-wallet/QRIS/card. "Tunai" (cash at pickup) is rejected for `fulfilment: antar` — no COD. `orders.payment_method` starts as literal `'online'` and gets overwritten with the real DOKU channel id (e.g. `VIRTUAL_ACCOUNT_BCA`) once the webhook reports success.

**Refund is recorded, never called via DOKU's API** (`InvoiceController::refund()`, permission `Pesanan:Refund`) — DOKU's refund endpoint only covers card payments, not VA/e-wallet/QRIS, so there's no single API call that generalizes across channels DOKU Checkout actually offers. An admin marks it refunded with a note after returning funds by whatever means actually applies; stock is *not* auto-returned (a refund often happens post-fulfillment).

**No real DOKU credentials exist in this environment** — `DOKU_CLIENT_ID`/`DOKU_SECRET_KEY` are blank in `.env`. Verified against the real `api-sandbox.doku.com` with junk credentials: DOKU responded with a structured `invalid_client_id` error, confirming the request/signature format is correct — only real merchant credentials are missing before this goes live.
