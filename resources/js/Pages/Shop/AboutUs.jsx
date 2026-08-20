import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';

/**
 * "Tentang Kami" (ROADMAP.md Fase 9.1) — nama badan usaha dan kontak resmi.
 *
 * The legal entity name/registration numbers are placeholders in brackets —
 * this app has never had a real NIB/company name to put here (ROADMAP.md
 * Fase 0.1 lists that as an unresolved business decision, not a coding one).
 * Filling in a fabricated-looking number would be worse than an honest
 * placeholder a real deployment must replace before launch.
 */
export default function AboutUs() {
    return (
        <MobileLayout title="Tentang Kami" header={<AppBar title="Tentang Kami" back="/ui/profile" tone="brand" />}>
            <div className="flex-1 overflow-y-auto p-4">
                <div className="mb-5">
                    <div className="mb-2.5 border-b border-line pb-2 font-display text-[15px]">
                        Inofarma
                    </div>
                    <p className="text-xs leading-[1.7] text-muted">
                        Inofarma adalah jaringan apotek dengan 10 cabang di wilayah Jabodetabek,
                        menjual obat bebas dan obat bebas terbatas serta kebutuhan kesehatan
                        sehari-hari — diantar dari cabang terdekat Anda atau diambil langsung di
                        toko.
                    </p>
                </div>

                <div className="mb-5">
                    <div className="mb-2.5 border-b border-line pb-2 font-display text-[15px]">
                        Badan Usaha
                    </div>
                    <p className="text-xs leading-[1.7] text-muted">
                        Dioperasikan oleh [Nama PT — isi nama badan usaha resmi terdaftar di
                        OSS/Kemenkumham]
                        <br />
                        NIB: [isi nomor induk berusaha]
                        <br />
                        NPWP: [isi NPWP badan usaha]
                    </p>
                </div>

                <div className="mb-5">
                    <div className="mb-2.5 border-b border-line pb-2 font-display text-[15px]">
                        Kontak Resmi
                    </div>
                    <p className="text-xs leading-[1.7] text-muted">
                        Layanan Pelanggan: cs@inofarma.co.id
                        <br />
                        Privasi Data: privasi@inofarma.co.id
                        <br />
                        Alamat korespondensi: [isi alamat kantor pusat resmi]
                    </p>
                </div>

                <div>
                    <div className="mb-2.5 border-b border-line pb-2 font-display text-[15px]">
                        Perizinan
                    </div>
                    <p className="text-xs leading-[1.7] text-muted">
                        Setiap cabang beroperasi dengan SIA (Surat Izin Apotek) dan Apoteker
                        Penanggung Jawab dengan SIPA aktif sendiri-sendiri — lihat detail izin dan
                        kontak apoteker tiap cabang di halaman{' '}
                        <a href="/ui/cabang-kami" className="text-brand underline">
                            Cabang Kami
                        </a>
                        .
                    </p>
                </div>
            </div>
        </MobileLayout>
    );
}
