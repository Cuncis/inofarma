---
paths:
  - resources/js/Components/Admin/BarChart.jsx
---

# Admin

## Charts: layout in real elements, and dark mode needs brand-lift
Do not draw charts with a fixed SVG viewBox stretched by `preserveAspectRatio="none"`. It scales x and y by different factors, which distorts axis text and turns `rx` corners into ellipses — that is what made the old revenue chart look broken. Lay bars out as flex children with percentage heights so nothing is scaled non-uniformly.

`brand` (#0900AA) scores **1.2:1 against the dark chart surface** — effectively invisible. Charts must use `dark:bg-brand-lift` (#6C63FF, validated at ≥3:1 and inside the lightness band). Never rely on an automatic light/dark flip for mark colors.

Chart series carry real units. Plot rupiah amounts as plain numbers and format with `moneyShort()` from `@/lib/format`; axis ticks come from a `niceMax()` round-up so they land on clean values.

Conventions the chart already follows, keep them: bars capped at 24px, 4px rounded top and square baseline, hairline solid gridlines, no legend for a single series, direct-label only the peak, per-bar hover/focus tooltip, and a "Lihat tabel" table view as the accessible twin.
