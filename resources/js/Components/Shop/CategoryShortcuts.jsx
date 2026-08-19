import { Link } from '@inertiajs/react';

/**
 * Home-page quick-category shortcuts, right below the hero carousel.
 *
 * Each icon is a real Apotek Inofarma category badge, self-hosted under
 * `public/media/images/categories/` (same reasoning as the hero carousel —
 * never depend on a third-party host staying online for storefront
 * artwork). The badges already render their own circular background, so
 * they're placed directly with no extra wrapper shape behind them.
 */
const CATEGORIES = [
    { label: 'Kesehatan', image: '/media/images/categories/kesehatan.png', href: '/ui/shop' },
    { label: 'Kebutuhan Keluarga', image: '/media/images/categories/kebutuhan-keluarga.png', href: '/ui/shop' },
    { label: 'Alat Kesehatan', image: '/media/images/categories/alat-kesehatan.png', href: '/ui/shop' },
    { label: 'Perawatan Tubuh', image: '/media/images/categories/perawatan-tubuh.png', href: '/ui/shop' },
    { label: 'Obat Tradisional', image: '/media/images/categories/obat-tradisional.png', href: '/ui/shop' },
    { label: 'Vitamin & Suplemen', image: '/media/images/categories/vitamin-suplemen.png', href: '/ui/shop' },
    { label: 'Obat Bebas', image: '/media/images/categories/obat-bebas.png', href: '/ui/shop' },
    { label: 'Semua Produk', image: '/media/images/categories/semua-produk.png', href: '/ui/shop' },
];

export default function CategoryShortcuts() {
    return (
        <div className="grid grid-cols-4 gap-y-3.5 px-3.5 py-3.5">
            {CATEGORIES.map((category) => (
                <Link
                    key={category.label}
                    href={category.href}
                    className="flex flex-col items-center gap-1.5 text-center"
                >
                    <img
                        src={category.image}
                        alt={category.label}
                        className="h-12 w-12 object-contain"
                    />

                    <span className="text-[10px] leading-tight text-ink">{category.label}</span>
                </Link>
            ))}
        </div>
    );
}
