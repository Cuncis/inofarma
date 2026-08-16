---
paths:
  - 'app/Support/**'
  - 'app/Http/Controllers/Admin/**'
---

# Support

## Referential integrity is the database's job now
Products hold `category_id` and `supplier_id`. Both are `restrictOnDelete`, so:

- **Renaming needs no cascade.** The screens read the name through the relation, so it follows automatically. Delete any code that rewrites names across rows.
- **Delete is refused while in use.** The controller counts first and turns a non-zero count into a `flash.error` with a readable message; MySQL would refuse it anyway. Keep both — the constraint is the guarantee, the check is the explanation.
- **Slugs and codes are the route keys**, not ids. `/admin/produk/{sku}`, `/admin/kategori/{slug}`, `/admin/penjual/{code}`, `/admin/pelanggan/{code}`, `/admin/pesanan/{number}`.

Orders snapshot their lines (`product_name`, `sku`, `unit_price`) and store their own totals. Never recompute an order's money from today's catalogue.

## The session stores are gone — Eloquent + presenters, and the vocabulary lives in AdminOptions
`App\Support\Catalog` and the five `*Store` classes were deleted in Fase 1.4. Do not reintroduce a session-backed repository; controllers talk to Eloquent directly.

Prop shaping lives in `App\Support\Presenters\*Presenter` — plain static classes, not JsonResource, so Inertia never wraps a `data` key. They emit the exact camelCase keys the React screens read (`id` is the human code: SKU, slug, or number — never the primary key).

`AdminOptions` is the only place that maps between the database's lowercase enums ('menunggu pembayaran') and the screens' Indonesian labels ('Menunggu Pembayaran'). Never hand-translate a status in a controller. `AdminOptions::stockLabel()` derives Tersedia/Stok Menipis/Habis from stock — that is a different field from a product's own status, and both are shown.

`CodeSequence::next()` and `Slug::unique()` must be handed a `withTrashed()` query: a soft-deleted row still holds its SKU/slug against the unique index.
