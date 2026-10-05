import useDragScroll from './useDragScroll';

/**
 * "Brand Terlaris" — real partner-brand logos carried at Apotek Inofarma,
 * self-hosted under `public/media/images/brands/` (same reasoning as the
 * hero carousel and category shortcuts — never depend on a third-party host
 * staying online for storefront artwork). Previously showed the catalogue's
 * `Product.manufacturer` values as lettered initials; none of the seeded
 * manufacturer names (placeholder Indonesian pharma companies) match these
 * real consumer brands, so this is now a curated list rather than
 * catalogue-derived.
 */
const BRANDS = [
    { name: 'Sido Muncul', image: '/media/images/brands/sido-muncul.png' },
    { name: 'Stimuno', image: '/media/images/brands/stimuno.png' },
    { name: 'Bayer', image: '/media/images/brands/bayer.png' },
    { name: 'Bisolvon', image: '/media/images/brands/bisolvon.png' },
    { name: 'Vicks', image: '/media/images/brands/vicks.png' },
    { name: 'My Baby', image: '/media/images/brands/my-baby.png' },
    { name: 'Panadol', image: '/media/images/brands/panadol.png' },
    { name: 'Cap Lang', image: '/media/images/brands/cap-lang.png' },
    { name: 'Counterpain', image: '/media/images/brands/counterpain.png' },
];

/**
 * `spread` lays the logos out as an even nine-column row filling the
 * container (desktop), instead of the scrolling strip of small circles.
 */
export default function BrandStrip({ className = 'px-3.5', spread = false }) {
    const drag = useDragScroll();

    if (spread) {
        return (
            <div className={`grid grid-cols-9 gap-5 ${className}`}>
                {BRANDS.map((brand) => (
                    <span
                        key={brand.name}
                        className="flex aspect-square items-center justify-center overflow-hidden rounded-full border border-line bg-white p-4"
                    >
                        <img src={brand.image} alt={brand.name} className="h-full w-full object-contain" />
                    </span>
                ))}
            </div>
        );
    }

    return (
        <div
            {...drag}
            className={`flex gap-3.5 overflow-x-auto scrollbar-none ${className} ${drag.className}`}
        >
            {BRANDS.map((brand) => (
                <div key={brand.name} className="flex w-16 shrink-0 items-center justify-center text-center">
                    <span className="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full border border-line bg-white p-1.5">
                        <img src={brand.image} alt={brand.name} className="h-full w-full object-contain" />
                    </span>
                </div>
            ))}
        </div>
    );
}
