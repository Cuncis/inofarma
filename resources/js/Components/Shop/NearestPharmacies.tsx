import { Link } from '@inertiajs/react';

/**
 * "Apotek Inofarma Terdekat" — a few storefronts shown as photo cards that
 * lead to "Cabang Kami". Photos are self-hosted under
 * `public/media/images/apotek/`, like the other storefront artwork.
 */
const PHARMACIES = [
    { name: 'Apotek Inofarma Jengki', image: '/media/images/apotek/jengki.png' },
    { name: 'Apotek Inofarma Kayu Manis', image: '/media/images/apotek/kayu-manis.png' },
    { name: 'Apotek Inofarma Pisangan Lama', image: '/media/images/apotek/pisangan-lama.png' },
    { name: 'Apotek Inofarma Kalisari', image: '/media/images/apotek/kalisari.png' },
];

export default function NearestPharmacies({ className = 'px-3.5', desktop = false }) {
    return (
        <div
            className={`${className} ${
                desktop
                    ? 'grid grid-cols-4 gap-4'
                    : 'flex snap-x gap-3 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden'
            }`}
        >
            {PHARMACIES.map((pharmacy) => (
                <Link
                    key={pharmacy.name}
                    href="/cabang-kami"
                    className={`block text-left ${desktop ? '' : 'w-[62%] shrink-0 snap-start'}`}
                >
                    <div className="aspect-[361/314] overflow-hidden rounded-2xl bg-canvas">
                        <img src={pharmacy.image} alt={pharmacy.name} loading="lazy" className="h-full w-full object-cover" />
                    </div>
                    <span className="mt-2 block text-[13px] font-semibold text-ink">{pharmacy.name}</span>
                </Link>
            ))}
        </div>
    );
}
