---
paths:
  - 'app/Support/Cart/**,app/Http/Controllers/Shop/CartController.php,app/Http/Controllers/Shop/CheckoutController.php,app/Models/Cart.php,app/Models/CartItem.php'
---

# Models Models

## Cart is DB-backed for customers, session-backed for guests — always go through CartManager
Fase 5.3. `carts`/`cart_items` only ever hold a signed-in customer's cart; a guest's cart lives in the session under `guest_cart` (`{branch_id, items: {productId: qty}}`). Never query `Cart`/`CartItem` directly from a controller or read/write the session key by hand — always go through `App\Support\Cart\CartManager`, which branches on `auth('customer')` internally and exposes one shape (`{branch, address, coupon, lines}`) either way. `CartManager::mergeGuestIntoCustomer()` runs from `Shop\AuthController@login` right after `Auth::guard('customer')->attempt()` succeeds.

Checkout requires signing in (`customer` middleware on `/ui/checkout`, `/ui/shipping-details`, `/ui/my-address`, coupon routes) — Fase 0's "boleh checkout sebagai tamu?" is still an open decision, and requiring an account sidesteps it without foreclosing either answer later. Only add-to-cart/update/remove work for guests.

One cart = one branch (ROADMAP.md 3.3). `CartManager::addItem()` throws `CartBranchConflictException` when a non-empty cart already holds a different branch and `$switchBranch` isn't passed; `Shop\CartController@store` turns that into a `branch` validation error the frontend offers to resolve by resubmitting with `switchBranch: true` (see `BranchPicker.jsx`).

`CheckoutController::store()` consumes stock immediately via `StockAllocator::consume()` (FEFO, batch-tracked), not `branch_stocks.reserved_quantity` — that column is still unwritten anywhere in the app and stays reserved for Fase 6/7's payment-expiry and pickup-expiry auto-release. The FEFO manifest is saved to `order_items.batches_consumed` (same shape as `stock_transfers.batches_shipped`) so `Shop\OrderController::cancel()` can hand stock back to the exact batches it came from via `StockAllocator::receive()`.

Coupons: `orders.coupon_id` + `coupons.used_count` is the only bookkeeping — one-per-customer is enforced by checking for a prior non-cancelled `Order` with that `coupon_id`+`customer_id`, not a separate redemptions table (see `coupons` migration docblock). Cancelling an order must decrement `used_count` back.

Delivery pricing (`App\Support\Cart\DeliveryPricing`) is a flat Rp 10.000 placeholder — real courier quotes are Fase 7. The radius *rejection* is real (Haversine between branch and address coordinates), only the fee is fake. Tax is hardcoded to 0 (`CheckoutController::TAX_RATE`) pending Fase 0's PPN decision.
