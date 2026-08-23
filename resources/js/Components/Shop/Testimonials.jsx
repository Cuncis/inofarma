import Icon from './Icon';
import useDragScroll from './useDragScroll';

const TESTIMONIALS = [
    {
        name: 'Satara Jufry',
        branch: 'Apotek Inofarma Jengki',
        quote: 'baru kali ini review apotek karena sangat impressed dengan pelayanannya! :) btw, saya beli online via chat customer care di WhatsApp.',
    },
    {
        name: 'Irfan',
        branch: 'Apotek Inofarma Kayu Manis',
        quote: 'Obat-obatannya lengkap, Banyak promo & Harganya sudah murah ditambah diskon lagi klo kita daftar jadi member, Gratis lagi cuma sebutkan nama dan nomer handphone, Cocok bgt buat langganan yg cari obat dengan resep dokter atau tanpa resep dokter.',
    },
    {
        name: 'Jia Nur',
        branch: 'Apotek Inofarma Pisangan Lama',
        quote: 'Pelayanan ramah dan cepat, farmasinya informatif dan membatu menjelaskan obat dengan jelas, stock juga cukup lengkap, bersih, dan sangat memudahkan saat butuh obat mendesak👍🏻',
    },
    {
        name: 'Ismi Khairani',
        branch: 'Apotek Inofarma Kalisari',
        quote: 'Suka banget belanja obat disini, pelayanannya ramah, apalagi kakak nya yang agak muda itu, ramah banget bintang 7 deh kalo bisa.',
    },
];

/**
 * "Testimoni Sobat Ino" — a horizontal-scrolling strip of real customer
 * reviews, one per Apotek Inofarma branch. Static/curated copy, same as
 * `BrandStrip` and `BenefitsGrid` — there's no testimonial model backing the
 * storefront catalogue.
 */
export default function Testimonials() {
    const drag = useDragScroll();

    return (
        <div
            {...drag}
            className={`flex gap-3 overflow-x-auto px-3.5 pb-1 scrollbar-none ${drag.className}`}
        >
            {TESTIMONIALS.map((testimonial) => (
                <div
                    key={testimonial.name}
                    className="flex w-64 shrink-0 flex-col gap-2 rounded-lg border border-line bg-white p-3.5"
                >
                    <div className="flex gap-0.5 text-star">
                        {Array.from({ length: 5 }).map((_, index) => (
                            <Icon key={index} name="star" size={13} />
                        ))}
                    </div>

                    <p className="line-clamp-5 text-xs leading-relaxed text-ink">
                        {testimonial.quote}
                    </p>

                    <div className="mt-auto pt-1">
                        <div className="text-[13px] font-bold text-ink">{testimonial.name}</div>
                        <div className="text-[11px] text-muted">{testimonial.branch}</div>
                    </div>
                </div>
            ))}
        </div>
    );
}
