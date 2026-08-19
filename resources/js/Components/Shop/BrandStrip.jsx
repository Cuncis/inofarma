import useDragScroll from './useDragScroll';

/**
 * "Brand Terlaris" — real manufacturer names pulled from the catalogue
 * (`Product.manufacturer`, Fase 4.2), not fixture data. There's no brand
 * logo artwork in this repo yet, so each chip shows the brand's initial in
 * a circle — swap in real logos here later the same way the category icons
 * are meant to be swapped in.
 *
 * @param {{ brands: string[] }} props
 */
export default function BrandStrip({ brands }) {
    const drag = useDragScroll();

    if (brands.length === 0) {
        return null;
    }

    return (
        <div
            {...drag}
            className={`flex gap-3.5 overflow-x-auto px-3.5 scrollbar-none ${drag.className}`}
        >
            {brands.map((brand) => (
                <div key={brand} className="flex w-16 shrink-0 flex-col items-center gap-1.5 text-center">
                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-blush font-display text-lg text-brand">
                        {brand.charAt(0).toUpperCase()}
                    </span>
                    <span className="line-clamp-2 text-[10px] leading-tight text-ink">{brand}</span>
                </div>
            ))}
        </div>
    );
}
