---
paths:
  - 'database/migrations/**'
---

# Migrations

## Schema rules: stock is per branch, money is integer rupiah
**Never add a `stock` column to `products`.** Stock belongs to the product × branch pair and lives in `branch_stocks` (unique on `branch_id, product_id`). Sellable quantity is `quantity - reserved_quantity` — reserved covers orders placed but not yet handed over. A single stock number on the product destroys the multi-branch model.

**Money is `unsignedBigInteger` in whole rupiah.** Never float, never decimal. `order_items.unit_price` and `orders.*_total` are snapshots written at order time and never recomputed from live data.

**Order line items snapshot `product_name`, `sku` and `unit_price`.** `order_items.product_id` is `SET NULL` on delete so history survives a product being removed.

Delete rules that encode business rules, keep them: `products.category_id`/`supplier_id` RESTRICT; `orders.branch_id`/`customer_id` RESTRICT; `branch_stocks` and `product_images` CASCADE.

Batches drive FEFO — always pick stock ordered by `expires_at`, never by id or quantity.

Tests run on SQLite while production is MySQL 8. Guard driver-specific DDL with `DB::getDriverName() === 'mysql'` (the products fulltext index already does this) or `migrate` will fail under test.

`users` is admin staff only; customers have their own table and guard. Both use soft deletes so audit attribution survives.
