---
paths:
  - resources/js/lib/catalog.ts
  - resources/js/lib/media.ts
  - resources/js/Components/Shop/data.ts
  - resources/js/Components/Shop/ProductFulfilment.tsx
---

# Lib

## The catalogue comes from the database, not from a file
`resources/js/lib/catalog.ts` no longer holds products. It holds the *shape* of the catalogue and the derived views (`useCatalog`, `findProduct`, `bestSellers`, `discounted`). The data arrives as the shared Inertia prop `catalog`, built by `App\Support\Presenters\ShopCatalogPresenter` and shared from `HandleInertiaRequests::share()`.

Never reintroduce a module-level product array. It was the reason an admin edit did not show up in the shop, and `StorefrontCatalogTest` exists to catch it coming back.

Because the catalogue is a prop, anything derived from it must be read **inside a component**, not at module load. The storefront's derived lists come from `useShopCatalog()` in `Components/Shop/data.ts`; the admin's global search takes the catalogue as an argument (`searchAdmin(query, catalog)`).

Prices are plain rupiah numbers, never formatted strings. Format at display time with `money()` from `@/lib/format`; the mobile grids want strings, so `useShopCatalog()` maps through a local `toTile()` adapter — extend that rather than putting strings in the prop.

`media` moved to `resources/js/lib/media.ts`. Product and category images now come back from the server with the record; `media` is only for fixtures still local to the front end (avatars, brand marks). `Components/Admin/data.ts` re-exports it as `img`.

The storefront's `status` is **availability** (Tersedia / Stok Menipis / Habis) derived from stock across all branches — not the catalogue status an administrator sets. Only `aktif` products are shared with the shop at all.

The catalogue is a pharmacy: products carry `unit`, `prescription`, `blurb` and `variants`. The original template's clothing concepts — colour swatches, XS-XL sizes, fashion tags — were removed, not repurposed. Don't reintroduce them.

## Product page fulfilment: no branch dropdown; delivery branch is data (supports_delivery), pickup uses a popup
ProductFulfilment (desktop BuyBox and phone BranchPicker) shows "Dikirim dari: <the branch with supports_delivery>" linking to /shop, and "Ambil di toko" with a "Lokasi pengambilan" popup (PickupLocationModal) over the supportsPickup branches. There is no branch dropdown. The branch that add-to-cart uses is the picked pickup branch, defaulting to the delivering branch if it has stock (defaultBranch in useProductBranches.ts); the cart is still bound to that one branch, so picking a non-delivering pickup branch means pickup only at checkout. Which branch delivers is NOT hard-coded: it is Branch.supports_delivery ("Melayani antar" in Cabang). Right now only Apotek Inofarma Pisangan Lama has it on (migration limit_home_delivery_to_pisangan_lama and BranchSeeder). /api/cabang/untuk-produk/{sku} lists every active branch; one without coordinates has distanceKm null instead of disappearing (Branch::scopeWithDistanceFrom). Distances in the popup need branch latitude/longitude filled in the admin. Postal code search goes through GET /api/lokasi/kode-pos (Nominatim, cached a month, throttled) and the point is saved with POST /lokasi.
