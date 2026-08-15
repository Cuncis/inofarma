---
paths:
  - 'public/**'
---

# Public

## Never create a public/ directory that shares a name with a route prefix
`public/.htaccess` skips the rewrite to `index.php` when the request maps to a real directory (`RewriteCond %{REQUEST_FILENAME} !-d`). So a directory named `public/admin/` makes the `/admin` route return 404 — the web server tries to serve the directory and never reaches Laravel. Child routes like `/admin/produk` still work, which makes it look like only the index page is broken.

Admin theme assets therefore live in `public/admin-assets/`, not `public/admin/`. Apply the same rule to any future asset drop.

Verify routes with real HTTP (`php artisan serve` + curl), not `app()->handle(Request::create(...))` — the latter bypasses the web server and its rewrite rules entirely, so it reports 200 for routes a browser cannot reach.
