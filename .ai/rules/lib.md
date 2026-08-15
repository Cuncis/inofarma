---
paths:
  - resources/js/lib/catalog.js
---

# Lib

## One catalogue feeds both the storefront and the admin
`resources/js/lib/catalog.js` is the single source of truth for products and categories. The storefront (`Components/BeShop/data.js`) and the admin (`Components/Admin/data.js`) both derive their views from it — neither keeps its own product list. Add or edit a product there and both areas update.

Prices are stored as plain rupiah numbers, never formatted strings. Format at display time with `money()` from `@/lib/format`. BeShop's grids want pre-formatted strings, so its `data.js` maps through a local `toTile()` adapter — extend that rather than putting strings in the catalogue.

Shared images live in `public/media/` and are addressed through the `media` helper exported from the catalogue; `Components/Admin/data.js` re-exports it as `img`.

The catalogue is a pharmacy: products carry `variants` (pack sizes), `unit`, `prescription` and `blurb`. The original template's clothing concepts — colour swatches, XS-XL sizes, fashion tags — were removed, not repurposed. Don't reintroduce them.
