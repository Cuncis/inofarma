---
paths:
  - resources/js/lib/catalog.js
  - resources/js/lib/media.js
  - resources/js/Components/Shop/data.js
---

# Lib

## The catalogue comes from the database, not from a file
`resources/js/lib/catalog.js` no longer holds products. It holds the *shape* of the catalogue and the derived views (`useCatalog`, `findProduct`, `bestSellers`, `discounted`). The data arrives as the shared Inertia prop `catalog`, built by `App\Support\Presenters\ShopCatalogPresenter` and shared from `HandleInertiaRequests::share()`.

Never reintroduce a module-level product array. It was the reason an admin edit did not show up in the shop, and `StorefrontCatalogTest` exists to catch it coming back.

Because the catalogue is a prop, anything derived from it must be read **inside a component**, not at module load. The storefront's derived lists come from `useShopCatalog()` in `Components/Shop/data.js`; the admin's global search takes the catalogue as an argument (`searchAdmin(query, catalog)`).

Prices are plain rupiah numbers, never formatted strings. Format at display time with `money()` from `@/lib/format`; the mobile grids want strings, so `useShopCatalog()` maps through a local `toTile()` adapter — extend that rather than putting strings in the prop.

`media` moved to `resources/js/lib/media.js`. Product and category images now come back from the server with the record; `media` is only for fixtures still local to the front end (avatars, brand marks). `Components/Admin/data.js` re-exports it as `img`.

The storefront's `status` is **availability** (Tersedia / Stok Menipis / Habis) derived from stock across all branches — not the catalogue status an administrator sets. Only `aktif` products are shared with the shop at all.

The catalogue is a pharmacy: products carry `unit`, `prescription`, `blurb` and `variants`. The original template's clothing concepts — colour swatches, XS-XL sizes, fashion tags — were removed, not repurposed. Don't reintroduce them.
