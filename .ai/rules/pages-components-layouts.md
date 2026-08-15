---
paths:
  - 'resources/js/{Pages,Components,Layouts}/Admin*/**'
---

# Pages Components Layouts

## Admin area: Tailwind rewrite of the Larkon theme
The admin was converted from the Bootstrap `dist/` theme into pure Tailwind — no Bootstrap CSS, no vendor JS plugins. Build screens from the shared kit in `Components/Admin` (Card, Table, TableToolbar, Badge, Button, RowActions, StatCard, Form, EntityForm, ListPage, Dropdown, BarChart) rather than hand-rolling markup; `ListPage` covers a plain search+table screen and `EntityForm` covers a plain field form.

Icons are baked in, not fetched. `Components/Admin/iconData.js` holds SVG bodies keyed by their original Iconify names (`solar:eye-broken`); render with `<Icon name="..." />`. To add one, pull it from `https://api.iconify.design/<prefix>.json?icons=<names>` and follow the `aliases` map — the theme uses misspelled aliases like `solar:magnifer-linear`.

Colours come from `admin.*` tokens with `dark:admin-dark-*` counterparts; every surface needs both. Bright `success`/`warning` are fills only — use `-deep` shades for text and `text-ink` on solid green/orange.

Routes live in the `$adminScreens` map in `routes/web.php` and must stay in sync with `Components/Admin/nav.js`. Paths are Indonesian slugs (`/admin/produk/ubah`).

Interactivity is prototype-level: local `useState`, `router.visit()` on submit, no persistence.
