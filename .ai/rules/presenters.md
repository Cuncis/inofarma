---
paths:
  - 'app/Observers/**,app/Notifications/**,app/Support/Notifications/**,app/Support/Money.php,app/Console/Commands/NotifyExpiringBatches.php,app/Console/Commands/NotifyApproachingPickupDeadlines.php,app/Http/Controllers/Admin/NotificationController.php,app/Support/Presenters/AdminNotificationPresenter.php'
---

# Presenters

## Order/notification architecture (Fase 8): observer-driven, never a scattered ->notify()
Every customer-facing order notification (confirmed, paid, shipped, ready for pickup, completed, cancelled) fires from exactly one place: `App\Observers\OrderObserver`, reacting to `Order::created`/`updated` (registered in `AppServiceProvider::boot()`). Never add a `->notify()` call inside `CheckoutController`, `DokuPaymentService`, `ShipmentService`, `PickupCodeService`, `OrderCancellation`, or `Admin\OrderController` — add the trigger condition to the observer instead, or a new order status/notification will be missed by whichever of those six call sites you didn't think to update. Same pattern for stock: `App\Observers\BranchStockObserver` is the only place `LowStock` fires from, on the transition *into* low stock (compare `getOriginal()` vs current), not on every write.

Admin-facing notifications (`App\Notifications\Admin\*`) are database-channel only — they back the topbar bell (`useAdminNotifications` + `AdminNotificationPresenter`), never email. Customer-facing notifications (`App\Notifications\*`, no sub-namespace) are mail (+ WhatsApp for the two ROADMAP.md Fase 8 calls out: `OrderShipped`, `OrderReadyForPickup`) — never database, a customer has no bell to read it from.

WhatsApp is Meta's own Cloud API (`App\Support\Notifications\WhatsAppClient`), hand-rolled like DOKU/Biteship — no third-party WA package. Business-initiated messages require a pre-approved template (`services.whatsapp.templates.*`); free-form text only works inside a customer-opened 24h window, which an order notification can't rely on. Unconfigured (`WHATSAPP_TOKEN` empty) falls back to `Log::info()`, matching the phone-OTP convention from Fase 3 — this app has never had real SMS/WhatsApp credentials in any phase.

The two scheduled reminder sweeps (`notifikasi:produk-kedaluwarsa`, `notifikasi:pengambilan-mendekati-batas`) each need a "already notified" timestamp column (`inventory_batches.expiry_reminder_sent_at`, `orders.pickup_reminder_sent_at`) to avoid renotifying the same row every run — and that column MUST be added to the model's `$fillable` array or `->update()` silently drops it (Laravel doesn't throw by default; `Model::preventSilentlyDiscardingAttributes()` isn't enabled in this app). This bit us once already when building Fase 8 — check `$fillable` first whenever a migration adds a column meant to be mass-assigned via `->update()`.

All notification classes `implements ShouldQueue` — `QUEUE_CONNECTION=database` is the app default, `sync` in tests (`phpunit.xml`). Money in a notification body renders via `App\Support\Money::rupiah()`, the PHP-side counterpart to `Components/{Shop,Admin}/data.js`'s `money()`.
