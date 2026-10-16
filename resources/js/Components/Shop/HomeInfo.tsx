/**
 * The home page's closing sections: two short centered texts about Apotek
 * Inofarma and a "Hubungi Inofarma Kapan Saja" contact block. Static copy,
 * like `BenefitsGrid` and `TestimonialSlider`.
 */
const TEXTS = [
    {
        title: 'Apotek Inofarma: Solusi Kesehatan Terhemat dan Terlengkap',
        paragraphs: [
            'Apotek Inofarma hadir sebagai solusi kesehatan terpercaya dengan jaringan apotek yang mudah dijangkau dan layanan yang selalu mengutamakan kebutuhan pelanggan. Kami menyediakan produk kesehatan terlengkap, vitamin, suplemen, dan perawatan diri dengan harga terhemat dan layanan yang bersahabat. Apotek Inofarma juga memberikan konsultasi obat gratis dan cek kesehatan dasar dengan apoteker profesional. Kami berkomitmen untuk selalu menghadirkan program dan penawaran menarik, siap membantu Anda menjaga kesehatan dengan cara yang mudah, aman, dan praktis.',
        ],
    },
    {
        title: 'Belanja Obat Praktis dengan Layanan Antar 24 Jam',
        paragraphs: [
            'Belanja produk kesehatan kini lebih mudah bersama Apotek Inofarma. Dengan layanan apotek buka 24 jam dan antar obat gratis*, kami siap memenuhi kebutuhan kesehatan Anda kapan saja, di mana saja. Pemesanan obat dapat dilakukan dengan mudah melalui WhatsApp, sehingga Anda dapat menerima obat dengan cepat tanpa repot. Apotek Inofarma juga menyediakan tes darah cepat (asam urat, gula darah, kolesterol total) dengan harga terjangkau untuk membantu Anda memantau kesehatan secara berkala. Selain itu, pelanggan setia Sobat Ino dapat menikmati berbagai benefit member eksklusif (untuk pembelian di outlet). Semua produk dijamin asli, terdaftar BPOM, dan berkualitas tinggi. Bersama Apotek Inofarma, kesehatan selalu lebih dekat, hemat, dan bersahabat.',
            '*S&K berlaku',
        ],
    },
];

function PhoneIcon() {
    return (
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M17 15l-3 3-8-8 3-3-5-5-3 3c0 9.941 8.059 18 18 18l3-3-5-5z" stroke="#0900aa" strokeWidth="1.5" strokeLinecap="square" />
            <path d="M14 1c4.971 0 9 4.029 9 9m-9-5c2.761 0 5 2.239 5 5" stroke="#24ce30" strokeWidth="1.5" strokeLinecap="square" />
        </svg>
    );
}

function EmailIcon() {
    return (
        <svg width="28" height="28" viewBox="0 0 22 22" fill="none" aria-hidden="true">
            <path d="M.916667 10.08333367l3.66666667-2.65833334v4.65849997zm20.1666667 0L17.416667 7.42500033v4.65849997z" stroke="#24ce30" />
            <path d="M4.58333367 7.42500033L.916667 10.08333367V21.0833337h20.1666667V10.08333367L17.416667 7.42500033" stroke="#0900aa" strokeWidth="1.5" />
            <path d="M4.58333367 12.1000003V.916667H17.416667v11.1833333m-16.5-2.01666663L21.0833337 21.0833337m0-11.00000003L11.0000003 15.5833337" stroke="#0900aa" strokeWidth="1.5" />
            <path d="M8.25000033 5.50000033h5.49999997M8.25000033 9.166667h5.49999997" stroke="#24ce30" strokeWidth="1.5" strokeLinecap="square" />
        </svg>
    );
}

const CONTACTS = [
    {
        icon: <PhoneIcon />,
        title: 'Layanan Pelanggan Inofarma',
        lines: ['Email: cs@inofarma.com', 'WhatsApp: 0812-1401-8964', 'Phone: (021) 50959952'],
    },
    {
        icon: <EmailIcon />,
        title: 'Layanan Pengaduan Konsumen',
        lines: [
            'Direktorat Jenderal Perlindungan Konsumen Tertib Niaga',
            'Kementerian Perdagangan RI',
            'WhatsApp: 0853-1111-1010',
        ],
    },
];

export default function HomeInfo({ className = 'px-3.5' }) {
    return (
        <div className={`${className} mx-auto max-w-[760px] text-center`}>
            {TEXTS.map((text) => (
                <section key={text.title} className="mt-8">
                    <h2 className="font-display text-[18px] leading-snug text-brand lg:text-[24px]">{text.title}</h2>
                    {text.paragraphs.map((paragraph) => (
                        <p key={paragraph} className="mt-3 text-[12.5px] leading-relaxed text-muted lg:text-[14px]">
                            {paragraph}
                        </p>
                    ))}
                </section>
            ))}

            <section className="mt-10">
                <h2 className="font-display text-[18px] leading-snug text-brand lg:text-[24px]">Hubungi Inofarma Kapan Saja</h2>
                <p className="mt-3 text-[12.5px] leading-relaxed text-muted lg:text-[14px]">
                    Hubungi kami untuk pertanyaan seputar produk, pesanan, maupun layanan dari Apotek Inofarma. Kami selalu siap untuk membantu kebutuhan kesehatan Anda.
                </p>

                <div className="mt-5 grid gap-3 text-left lg:grid-cols-2 lg:gap-4">
                    {CONTACTS.map((contact) => (
                        <div key={contact.title} className="flex items-start gap-3 rounded-2xl border border-line bg-white p-4">
                            <span className="mt-0.5 shrink-0">{contact.icon}</span>
                            <div>
                                <p className="text-[13px] font-bold text-ink">{contact.title}</p>
                                {contact.lines.map((line) => (
                                    <p key={line} className="mt-1 text-[12.5px] text-muted">{line}</p>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            </section>
        </div>
    );
}
