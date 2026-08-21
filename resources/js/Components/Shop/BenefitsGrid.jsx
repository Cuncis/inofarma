/**
 * "Keuntungan Belanja di Inofarma" — nine reasons to shop here, each using a
 * real Apotek Inofarma benefit badge, self-hosted under
 * `public/media/images/benefits/` (same reasoning as the hero carousel and
 * category shortcuts — never depend on a third-party host staying online for
 * storefront artwork).
 */
const BENEFITS = [
    { label: 'Produk Kesehatan Termurah', image: '/media/images/benefits/produk-kesehatan-termurah.png' },
    { label: 'Hemat Setiap Hari', image: '/media/images/benefits/hemat-setiap-hari.png' },
    { label: 'Produk Lengkap', image: '/media/images/benefits/produk-lengkap.png' },
    { label: 'Apotek Buka 24 Jam', image: '/media/images/benefits/apotek-buka-24-jam.png' },
    { label: 'Layanan Antar 24 Jam', image: '/media/images/benefits/layanan-antar-24-jam.png' },
    { label: 'Mudah Dijangkau', image: '/media/images/benefits/mudah-dijangkau.png' },
    { label: 'Konsultasi Gratis', image: '/media/images/benefits/konsultasi-gratis.png' },
    { label: 'Benefit Sobat Ino', image: '/media/images/benefits/benefit-sobat-ino.png' },
    { label: 'Belanja Praktis', image: '/media/images/benefits/belanja-praktis.png' },
];

export default function BenefitsGrid() {
    return (
        <div className="grid grid-cols-3 gap-3 px-3.5">
            {BENEFITS.map((benefit) => (
                <div
                    key={benefit.label}
                    className="flex flex-col items-center gap-1.5 border border-line bg-white px-2 py-3.5 text-center"
                >
                    <img src={benefit.image} alt={benefit.label} className="h-9 w-9 object-contain" />
                    <span className="text-[10px] leading-tight text-ink">{benefit.label}</span>
                </div>
            ))}
        </div>
    );
}
