---
paths:
  - 'resources/js/{Pages,Components}/BeShop/**'
---

# Be Shop

## BeShop UI copy is Indonesian; prices go through money()
All user-visible copy in the BeShop screens (labels, headings, placeholders, aria-labels, fixture data) is Indonesian — the target market is Indonesia. Keep new screens/strings in Indonesian too.

Never hardcode a price. Import `money()` from `@/Components/BeShop/data` and pass a rupiah amount as a plain number: `money(1350000)` renders `Rp 1.350.000` (`toLocaleString('id-ID')`). Fixture products store already-formatted `price`/`oldPrice` strings built via `money()`; `cartItems` store a numeric `amount` so quantities and discounts can be computed.

Route slugs stay English (`/ui/signin`, `/ui/order-history`) — only the display names in `screens.js` are translated. Don't rename slugs; `routes/web.php` mirrors them.

App locale is `id` (`APP_LOCALE`), with `lang/id/validation.php` supplying Indonesian validation messages. Fallback locale is still `en`, so untranslated rules degrade to English rather than showing raw keys.
