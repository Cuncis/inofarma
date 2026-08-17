---
paths:
  - 'app/Support/Presenters/InvoicePresenter.php,app/Http/Controllers/Admin/InvoiceController.php,app/Models/Coupon.php,app/Http/Controllers/Admin/CouponController.php'
---

# Http Controllers Admin

## Faktur is Order read as an invoice — never add an invoices table
"Faktur" has no table of its own. `InvoicePresenter` reads `Order` directly — `orders.payment_status` becomes the invoice status (lunas/refund/jatuh tempo derived from `expires_at`/belum bayar), `orders.expires_at` is the due date. `InvoiceController` only has `index`/`show`; there is deliberately no create/edit/delete route because a faktur isn't a record, it's a view. This was a Fase 4.3 decision specifically to avoid inventing a second "paid/unpaid" flag that Fase 6's real payment status would then have to reconcile with. If a future phase needs faktur-specific fields (e.g. a separate tax invoice number), extend `orders` or add a one-to-one `invoices` row keyed to `order_id` — don't make `Order` a child of a new `Invoice` parent.

`Coupon`'s branch scope follows the same "empty means everywhere" convention as `users.branch_id IS NULL`: no rows in the `coupon_branch` pivot means the coupon is valid at every branch (`Coupon::appliesToBranch()`). Redemption/one-per-customer enforcement isn't built yet — that's Fase 5's cart, not this model.
