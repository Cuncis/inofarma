import Icon from './Icon';

/**
 * "Keuntungan Belanja di Inofarma" — nine reasons to shop here, each mapped
 * to the closest existing icon in `Icon.jsx` rather than new artwork
 * (nothing was supplied for this section, unlike the hero/category images).
 */
const BENEFITS = [
    { label: 'Produk Kesehatan Termurah', icon: 'tag' },
    { label: 'Hemat Setiap Hari', icon: 'promo' },
    { label: 'Produk Lengkap', icon: 'check' },
    { label: 'Apotek Buka 24 Jam', icon: 'clock' },
    { label: 'Layanan Antar 24 Jam', icon: 'navigation' },
    { label: 'Mudah Dijangkau', icon: 'pin' },
    { label: 'Konsultasi Gratis', icon: 'message' },
    { label: 'Benefit Sobat Ino', icon: 'heart' },
    { label: 'Belanja Praktis', icon: 'cart' },
];

export default function BenefitsGrid() {
    return (
        <div className="grid grid-cols-3 gap-3 px-3.5">
            {BENEFITS.map((benefit) => (
                <div
                    key={benefit.label}
                    className="flex flex-col items-center gap-1.5 border border-line bg-white px-2 py-3.5 text-center"
                >
                    <Icon name={benefit.icon} size={22} className="text-brand" />
                    <span className="text-[10px] leading-tight text-ink">{benefit.label}</span>
                </div>
            ))}
        </div>
    );
}
