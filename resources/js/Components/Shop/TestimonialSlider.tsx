import { useState } from 'react';
import useIsDesktop from './useIsDesktop';

const TESTIMONIALS = [
    {
        name: 'Satara Jufry',
        branch: 'Apotek Inofarma Jengki',
        image: '/media/images/testimonials/sobat-ino-2.png',
        quote: 'baru kali ini review apotek karena sangat impressed dengan pelayanannya! :) btw, saya beli online via chat customer care di WhatsApp.',
    },
    {
        name: 'Irfan',
        branch: 'Apotek Inofarma Kayu Manis',
        image: '/media/images/testimonials/sobat-ino-1.png',
        quote: 'Obat-obatannya lengkap, Banyak promo & Harganya sudah murah ditambah diskon lagi klo kita daftar jadi member, Gratis lagi cuma sebutkan nama dan nomer handphone, Cocok bgt buat langganan yg cari obat dengan resep dokter atau tanpa resep dokter.',
    },
    {
        name: 'Jia Nur',
        branch: 'Apotek Inofarma Pisangan Lama',
        image: '/media/images/testimonials/sobat-ino-2.png',
        quote: 'Pelayanan ramah dan cepat, farmasinya informatif dan membatu menjelaskan obat dengan jelas, stock juga cukup lengkap, bersih, dan sangat memudahkan saat butuh obat mendesak👍🏻',
    },
    {
        name: 'Ismi Khairani',
        branch: 'Apotek Inofarma Kalisari',
        image: '/media/images/testimonials/sobat-ino-1.png',
        quote: 'Suka banget belanja obat disini, pelayanannya ramah, apalagi kakak nya yang agak muda itu, ramah banget bintang 7 deh kalo bisa.',
    },
];

/**
 * Centered testimonial slider: one card per view on a phone, two on desktop,
 * with previous/next arrows and page dots. Static copy, like `BrandStrip` and `BenefitsGrid`.
 */
export default function TestimonialSlider({ className = 'px-3.5' }) {
    const isDesktop = useIsDesktop();
    const perView = isDesktop ? 2 : 1;
    const pageCount = Math.ceil(TESTIMONIALS.length / perView);
    const [requestedPage, setPage] = useState(0);
    const page = Math.min(requestedPage, pageCount - 1);

    return (
        <div className={className}>
            <div className="overflow-hidden">
                <div
                    className="flex transition-transform duration-300"
                    style={{ transform: `translateX(-${page * 100}%)` }}
                >
                    {TESTIMONIALS.map((testimonial) => (
                        <div key={testimonial.name} className="shrink-0 px-2" style={{ flexBasis: `${100 / perView}%` }}>
                            <div className="flex h-full flex-col items-center gap-3 rounded-2xl border border-line bg-white p-5 text-center">
                                <img
                                    src={testimonial.image}
                                    alt={testimonial.name}
                                    width={72}
                                    height={72}
                                    loading="lazy"
                                    className="h-[72px] w-[72px] rounded-full object-cover"
                                />
                                <h3 className="font-display text-[15px] text-ink">{testimonial.name}</h3>
                                <p className="text-[12.5px] leading-relaxed text-muted">{testimonial.quote}</p>
                                <p className="mt-auto text-[12px] font-semibold text-brand">{testimonial.branch}</p>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            <div className="mt-4 flex items-center justify-center gap-4">
                <button
                    type="button"
                    aria-label="Sebelumnya"
                    disabled={page === 0}
                    onClick={() => setPage(page - 1)}
                    className="flex h-8 w-8 items-center justify-center rounded-full border border-line text-ink disabled:opacity-30"
                >
                    <svg width="8" height="12" viewBox="0 0 8 12" fill="none" aria-hidden="true">
                        <path d="M6 10L2 6l4-4" stroke="currentColor" strokeWidth="2" />
                    </svg>
                </button>

                <div className="flex gap-2">
                    {Array.from({ length: pageCount }).map((_, index) => (
                        <button
                            key={index}
                            type="button"
                            aria-label={`Halaman ${index + 1}`}
                            onClick={() => setPage(index)}
                            className={`h-2 w-2 rounded-full ${index === page ? 'bg-brand' : 'bg-line'}`}
                        />
                    ))}
                </div>

                <button
                    type="button"
                    aria-label="Berikutnya"
                    disabled={page === pageCount - 1}
                    onClick={() => setPage(page + 1)}
                    className="flex h-8 w-8 items-center justify-center rounded-full border border-line text-ink disabled:opacity-30"
                >
                    <svg width="8" height="12" viewBox="0 0 8 12" fill="none" aria-hidden="true">
                        <path d="M2 2l4 4-4 4" stroke="currentColor" strokeWidth="2" />
                    </svg>
                </button>
            </div>
        </div>
    );
}
