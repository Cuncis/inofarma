---
paths:
  - 'app/Support/ProductCsvImporter.php,app/Http/Controllers/Admin/ProductImportController.php'
---

# Support Http Controllers Admin

## CSV product import: SKU/Handle are the idempotency key, not a fresh CodeSequence
`ProductCsvImporter` (Shopify product-export format) writes the CSV's `Variant SKU` straight into `products.sku` and `Handle` straight into `slug` — never a `PRD-` `CodeSequence` — so re-uploading the same export updates existing rows instead of duplicating them.

Scope is deliberately products only: no stock/branch/batch rows are created (`Exp Date` in `Body (HTML)` is parsed out and ignored — that's batch-level and belongs to a separate, deliberate stock-in action). `supplier_id` is left null rather than inventing a fake distributor for the CSV's "Vendor" (which is just the store's own name, not a real PBF).

`Golongan Obat: BLUE` → `drug_class = 'bebas terbatas'`, which legally requires a P1–P6 warning (`Product::needs_warning_label`). The CSV has no warning text, so those rows import as `status = 'nonaktif'` rather than going live unwarned — an admin must add the warning and activate by hand. `unit` and `indication` are best-effort (keyword/regex guesses from the packaging line and Deskripsi) — null/default rather than fabricated when no pattern matches.

Bypasses `ProductRequest` entirely (writes via Eloquent directly, like a seeder) since it's a bulk backend import, not a form submission — so `ProductRequest`'s `seller` (required) and `warning` (`required_if:drugClass,Bebas Terbatas`) rules don't block it; imported bebas-terbatas products stay `nonaktif` until an admin fills in `warning` and `seller` through the normal edit form.
