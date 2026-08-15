---
paths:
  - tailwind.config.js
---

# General

## Storefront palette tokens and the .js content glob
Three brand colours: `brand` #0900AA, `success` #24CE30, `warning` #FE7900. `blush` (#ebebf8) is a light tint of brand used for headers and auth panels; `star` and `sale` are aliases of the orange and green.

The bright green and orange are unreadable as text on light backgrounds (~2:1). Use the DEFAULT for fills, badges and icons; use `text-success-deep` / `text-warning-deep` for text. Solid green/orange badges take `text-ink`, never `text-white`.

The `content` globs must include `./resources/js/**/*.js`, not just `*.jsx` — `Components/Shop/data.js` holds Tailwind class names in the promocode `tone` fields, and they are silently dropped from the build otherwise.

Verify a palette change without vite: `node_modules/.bin/tailwindcss -c tailwind.config.js -i resources/css/app.css -o /tmp/out.css` and grep the emitted classes.
