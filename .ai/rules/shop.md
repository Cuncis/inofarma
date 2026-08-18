---
paths:
  - 'resources/js/{Pages,Components}/Shop/**'
---

# Storefront

## Storefront copy is Indonesian; prices go through money()
All user-visible copy in the storefront screens (labels, headings, placeholders, aria-labels, fixture data) is Indonesian — the target market is Indonesia. Keep new screens/strings in Indonesian too.

Never hardcode a price. Import `money()` from `@/Components/Shop/data` and pass a rupiah amount as a plain number: `money(1350000)` renders `Rp 1.350.000` (`toLocaleString('id-ID')`). Products come from `@/lib/catalog` as plain numbers; `Components/Shop/data.js` formats them through its `toTile()` adapter for the grids. The real cart/checkout/order screens (Fase 5) get their numbers from `CartPresenter`/`ShopOrderPresenter` props instead — see `app/Support/Cart/**`'s rule file for that side.

Route slugs stay English (`/ui/signin`, `/ui/order-history`) — only the display names in `screens.js` are translated. Don't rename slugs; `routes/web.php` mirrors them.

App locale is `id` (`APP_LOCALE`), with `lang/id/validation.php` supplying Indonesian validation messages. Fallback locale is still `en`, so untranslated rules degrade to English rather than showing raw keys.
