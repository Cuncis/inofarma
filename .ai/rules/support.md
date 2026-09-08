---
paths:
  - 'app/Support/**'
  - 'app/Http/Controllers/Admin/**'
  - 'app/Filament/**'
---

# Support

## Referential integrity is the database's job now
Products hold `category_id` and `supplier_id`. Both are `restrictOnDelete`, so:

- **Renaming needs no cascade.** The screens read the name through the relation, so it follows automatically. Delete any code that rewrites names across rows.
- **Delete is refused while in use.** Each Filament resource's `DeleteAction` counts first, in a `->before()` closure, and turns a non-zero count into a `Filament\Notifications\Notification` danger toast plus `$action->cancel()`; MySQL would refuse it anyway. Keep both — the constraint is the guarantee, the check is the explanation.
- **Filament resources bind records by primary key (`{record}`), not by slug/SKU/code.** This is a deliberate change from the legacy Inertia admin, which used human-readable route keys (`/admin/produk/{sku}`, `/admin/kategori/{slug}`, `/admin/pemasok/{code}`, `/admin/pelanggan/{code}`, `/admin/pesanan/{number}`) — those codes/slugs still exist as columns and are still generated the same way (`CodeSequence`/`Slug::unique()`), they just aren't what the URL binds on any more.

Orders snapshot their lines (`product_name`, `sku`, `unit_price`) and store their own totals. Never recompute an order's money from today's catalogue.

## The session stores were gone even before Filament — now the presenter layer is too
`App\Support\Catalog` and the five `*Store` classes were deleted in Fase 1.4; nothing reintroduced a session-backed repository. The Filament migration then removed the `App\Support\Presenters\*Presenter` classes that used to shape Eloquent models into the Inertia screens' prop arrays (`OrderPresenter`, `ProductPresenter`, `CategoryPresenter`, `CustomerPresenter`, `StaffPresenter`, `RolePresenter`, `BranchPresenter`, `InvoicePresenter`, `ReconciliationPresenter`, `StockTransferPresenter`) — a Filament table/form/infolist reads the Eloquent model directly, so that translation layer had no reason left to exist. The few Presenters still under this path (`ShopOrderPresenter`, `StorefrontBranchPresenter`, `ShopCatalogPresenter`, `CustomerPresenter` for the storefront, etc.) are Shop-side and untouched — don't confuse them for a surviving admin pattern.

`AdminOptions` is still the only place that maps between the database's lowercase enums ('menunggu pembayaran') and the Indonesian labels ('Menunggu Pembayaran') Filament tables/forms show. Never hand-translate a status inline. `AdminOptions::stockLabel()` derives Tersedia/Stok Menipis/Habis from stock — that is a different field from a product's own status, and both are shown.

`CodeSequence::next()` and `Slug::unique()` must be handed a `withTrashed()` query: a soft-deleted row still holds its SKU/slug against the unique index. Every Filament `CreateXxx`/`EditXxx` page still calls these the same way the old controllers did, from `mutateFormDataBeforeCreate()`/`mutateFormDataBeforeSave()`.
