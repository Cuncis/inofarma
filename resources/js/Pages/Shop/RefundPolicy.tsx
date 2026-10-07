import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';

/**
 * "Kebijakan Pengembalian Dana" (ROADMAP.md Fase 9.1) — proses uang kembali,
 * bukan proses tukar barang (itu ada di Syarat & Ketentuan §7, kebijakan
 * retur obat). Mengikuti mekanisme sungguhan Fase 6: refund dicatat manual
 * oleh admin cabang setelah dana benar-benar ditransfer kembali, karena
 * endpoint refund DOKU sendiri cuma mencakup pembayaran kartu.
 */
const sections = [
    {
        title: 'Kapan Dana Dikembalikan',
        body: 'Dana dikembalikan bila: pesanan dibatalkan sebelum diproses cabang, pesanan kedaluwarsa (melewati batas waktu bayar atau batas waktu ambil) padahal sudah terlanjur dibayar, atau klaim retur obat pada Syarat & Ketentuan §7 disetujui cabang.',
    },
    {
        title: 'Cara Dana Dikembalikan',
        body: 'Untuk pembayaran online lewat DOKU (transfer bank, e-wallet, QRIS, kartu), dana dikembalikan ke metode pembayaran yang sama yang Anda pakai saat membayar. Untuk pesanan yang dibayar tunai di kasir, dana dikembalikan tunai atau transfer ke rekening yang Anda konfirmasi ke cabang.',
    },
    {
        title: 'Berapa Lama',
        body: 'Pengembalian dana dicatat dan diproses oleh staf cabang setelah dana secara nyata sudah dikirim kembali, bukan otomatis lewat sistem pembayaran. Perkirakan 3-14 hari kerja tergantung metode pembayaran awal dan bank/e-wallet penerima. Status "Refund" pada halaman Lacak Pesanan berarti pengembalian sudah tercatat oleh cabang.',
    },
    {
        title: 'Stok pada Pesanan yang Di-refund',
        body: 'Stok yang sudah dikonsumsi untuk pesanan yang di-refund karena klaim retur tidak dikembalikan otomatis ke rak, karena barangnya sudah pernah berpindah tangan ke pelanggan. Untuk pembatalan/kedaluwarsa sebelum barang berpindah tangan, stok dikembalikan otomatis sejak awal.',
    },
    {
        title: 'Pertanyaan Status Refund',
        body: 'Hubungi cabang tempat pesanan Anda dibuat, nomor telepon dan WhatsApp cabang tersedia di halaman Cabang Kami dan pada halaman Lacak Pesanan Anda.',
    },
];

export default function RefundPolicy() {
    return (
        <MobileLayout
            title="Kebijakan Pengembalian Dana"
            header={<AppBar title="Pengembalian Dana" back="/ui/profile" tone="brand" />}
        >
            <div className="flex-1 overflow-y-auto p-4">
                {sections.map((section) => (
                    <div key={section.title} className="mb-5">
                        <div className="mb-2.5 border-b border-line pb-2 font-display text-[15px]">
                            {section.title}
                        </div>
                        <p className="text-xs leading-[1.7] text-muted">{section.body}</p>
                    </div>
                ))}
            </div>
        </MobileLayout>
    );
}
