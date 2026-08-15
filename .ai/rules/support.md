---
paths:
  - 'app/Support/**'
  - app/Support/CategoryStore.php
---

# Support

## ProductStore is the database stand-in — keep the seam clean
Admin product CRUD is backed by `App\Support\ProductStore`, a session-backed repository seeded from `App\Support\Catalog`. There is no database yet. Writes land in the session, so they survive navigation but not a new browser or another user.

Swapping in a real table means reimplementing the six methods on `ProductStore` (`all/find/create/update/delete/reset`) against an Eloquent model and deleting `Catalog`. Nothing in `ProductController` or the React pages should need to change — keep controller actions talking only to the repository, never to session directly.

Admin product pages take `products`/`product` as Inertia props; they must not import fixtures from `@/Components/Admin/data`. That file's `products` export is now only used by non-product screens.

KNOWN GAP: the storefront still reads the static JS catalogue (`@/lib/catalog`), so an admin edit does not yet show up in the shop. Closing it means sharing the store globally from `HandleInertiaRequests` and converting the Shop pages to read that prop.

## Categories are referenced by name — the store keeps that honest
Products store their category as a **name string**, not a foreign key. `CategoryStore` is what stops that going stale:

- **Rename cascades.** `update()` compares old and new name and rewrites every product in that category. Never write a category name straight to the session and skip this.
- **Delete is refused while in use.** `delete()` returns `false` when `productCount()` is non-zero; the controller turns that into a `flash.error`, not a success. The list screen also pre-empts it — the confirm dialog explains the block instead of firing a request that will fail.
- **Slugs are the identifier and must be unique.** `create()` runs `uniqueSlug()`, appending `-2`, `-3`… on collision. Routes are `/admin/kategori/{slug}`.

The product form and `ProductRequest` validation both read `CategoryStore->names()`, never `Catalog::categories()` — otherwise a newly created category could not be assigned to a product. `Catalog::categories()` returns full records now; use `Catalog::categoryNames()` if you only want the names.

When a real database arrives, swap the name column for a foreign key and these three behaviours become constraints instead of code.
