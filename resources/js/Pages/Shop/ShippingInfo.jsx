import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';

/**
 * "Kebijakan Pengiriman" (ROADMAP.md Fase 9.1) — replaces the original
 * click-through prototype's generic ecommerce placeholder (flat "free
 * shipping over Rp750rb", 30-day returns, Visa/Mastercard) with what this
 * app actually does: Biteship-quoted courier rates from the branch itself,
 * a real delivery radius, and DOKU's actual payment channels.
 */
const sections = [
    {
        title: 'Ongkos Kirim',
        body: 'Ongkos kirim dihitung langsung dari cabang yang Anda pilih ke alamat tujuan — bukan tarif rata, dan berbeda-beda tergantung jarak, berat, dan kurir yang Anda pilih saat checkout (termasuk kurir instan seperti Gojek/Grab untuk pengiriman cepat di sekitar cabang).',
    },
    {
        title: 'Area Pengiriman',
        body: 'Setiap cabang punya radius antar sendiri. Alamat di luar radius cabang yang Anda pilih tidak bisa diproses untuk pengiriman — pilih cabang lain yang lebih dekat dengan alamat Anda, atau pilih Ambil di Toko.',
    },
    {
        title: 'Estimasi Waktu Kirim',
        body: 'Estimasi waktu tiba mengikuti perkiraan dari kurir yang Anda pilih saat checkout, ditampilkan sebelum Anda membayar. Setelah pesanan diserahkan ke kurir, status pengiriman diperbarui otomatis sampai barang diterima.',
    },
    {
        title: 'Batas Waktu Pembayaran',
        body: 'Pesanan yang belum dibayar dalam 24 jam dibatalkan otomatis dan stok dikembalikan, supaya barang tidak tertahan untuk pesanan yang tidak jadi dibayar.',
    },
    {
        title: 'Ambil di Toko',
        body: 'Setelah pesanan disiapkan cabang, Anda mendapat kode ambil 6 digit (dan QR) yang berlaku 48 jam. Tunjukkan ke kasir untuk mengambil pesanan.',
    },
    {
        title: 'Metode Pembayaran',
        body: 'Pembayaran online diproses DOKU: transfer bank, e-wallet (GoPay, OVO, DANA, dll.), QRIS, dan kartu debit/kredit — semua diproses langsung di halaman DOKU, tidak lewat server kami. Pesanan Ambil di Toko juga bisa dibayar tunai saat pengambilan. Semua transaksi online dilindungi enkripsi standar industri milik DOKU.',
    },
];

export default function ShippingInfo() {
    return (
        <MobileLayout
            title="Kebijakan Pengiriman"
            header={<AppBar title="Kebijakan Pengiriman" back="/ui/profile" tone="brand" />}
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
