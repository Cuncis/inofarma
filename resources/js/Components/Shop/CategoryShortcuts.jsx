import { Link } from '@inertiajs/react';
import { shopCategories } from './data';

/**
 * Home-page quick-category shortcuts, right below the hero carousel.
 *
 * Each icon is a real Apotek Inofarma category badge, self-hosted under
 * `public/media/images/categories/` (same reasoning as the hero carousel —
 * never depend on a third-party host staying online for storefront
 * artwork). The badges already render their own circular background, so
 * they're placed directly with no extra wrapper shape behind them.
 *
 * Laid out as a table: only `border-r`/`border-b` on each cell plus
 * `border-l`/`border-t` on the grid itself, so shared edges between cells
 * never double up into a thicker line the way `border` on every cell would.
 *
 * Shares `shopCategories` with the "Kategori" page (`Categories.jsx`) so the
 * two screens always list the same set, in the same order.
 */
export default function CategoryShortcuts() {
    return (
        <div className="mx-3.5 my-3.5 grid grid-cols-4 border-l border-t border-line bg-white">
            {shopCategories.map((category) => (
                <Link
                    key={category.name}
                    href="/ui/shop"
                    className="flex flex-col items-center justify-center gap-1.5 border-b border-r border-line px-1.5 py-3.5 text-center"
                >
                    <img
                        src={category.image}
                        alt={category.name}
                        className="h-11 w-11 object-contain"
                    />

                    <span className="text-[10px] leading-tight text-ink">{category.name}</span>
                </Link>
            ))}
        </div>
    );
}
