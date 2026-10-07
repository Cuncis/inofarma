---
paths:
  - resources/css/app.css
---

# General

## Storefront palette tokens (Tailwind v4, defined in app.css)
The palette lives in the `@theme` block of `resources/css/app.css` (Tailwind v4 has no `tailwind.config.js`; dark mode is the `@custom-variant dark` line there). Three brand colours: `brand` #0900AA, `success` #24CE30, `warning` #FE7900. `blush` (#ebebf8) is a light tint of brand used for headers and auth panels; `star` and `sale` are aliases of the orange and green.

The bright green and orange are unreadable as text on light backgrounds (~2:1). Use the DEFAULT for fills, badges and icons; use `text-success-deep` / `text-warning-deep` for text. Solid green/orange badges take `text-ink`, never `text-white`.

Tailwind v4 detects class names by scanning the project's source files itself, so `Components/Shop/data.ts` (which holds Tailwind class names in the promocode `tone` fields) is picked up automatically. Extra scan locations are added with `@source` in `app.css`. Don't rename a Tailwind class inside a plain string that is also a prop value (e.g. the `Button` variant `outline`); the v3-to-v4 upgrade tool did that once and it had to be undone.

Verify a palette change without the dev server: `npx vite build` and grep the emitted CSS in `public/build/assets` for the classes.
