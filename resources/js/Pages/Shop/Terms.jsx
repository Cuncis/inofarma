import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';

/**
 * "Syarat & Ketentuan" (ROADMAP.md Fase 9.1), termasuk kebijakan retur obat.
 * Isi mengikuti mekanisme yang sungguhan ada di aplikasi ini (DOKU, Biteship,
 * jendela 24 jam/48 jam, golongan obat) — bukan teks generik toko daring.
 */
const sections = [
    {
        title: '1. Ruang Lingkup',
        body: 'Syarat & Ketentuan ini mengatur penggunaan aplikasi dan layanan Inofarma — jaringan apotek dengan cabang fisik yang menjual obat bebas dan obat bebas terbatas. Kami tidak menjual obat keras atau obat dengan resep dokter melalui aplikasi ini.',
    },
    {
        title: '2. Akun Pelanggan',
        body: 'Anda bertanggung jawab menjaga kerahasiaan kata sandi akun Anda. Data yang Anda daftarkan harus benar — nomor telepon diverifikasi lewat kode OTP karena dipakai untuk konfirmasi pesanan dan pengambilan di cabang.',
    },
    {
        title: '3. Pemesanan',
        body: 'Setiap pesanan berasal dari satu cabang yang Anda pilih sendiri, dan hanya bisa diantar atau diambil di cabang itu. Stok yang ditampilkan adalah stok cabang tersebut, bukan stok nasional. Pesanan yang belum dibayar dalam 24 jam dibatalkan otomatis dan stoknya dikembalikan.',
    },
    {
        title: '4. Pembayaran',
        body: 'Pembayaran daring diproses oleh DOKU (transfer bank, e-wallet, QRIS, kartu) — Inofarma tidak pernah menyimpan nomor kartu atau kredensial pembayaran Anda. Pesanan Ambil di Toko juga bisa dibayar tunai di kasir cabang saat pengambilan.',
    },
    {
        title: '5. Pengiriman',
        body: 'Pesanan Antar dikirim oleh kurir pihak ketiga (Biteship, termasuk kurir instan) dari cabang yang Anda pilih. Alamat di luar radius antar cabang tersebut tidak bisa dipilih untuk pengiriman — lihat Kebijakan Pengiriman untuk detail lengkap.',
    },
    {
        title: '6. Pengambilan di Toko',
        body: 'Pesanan Ambil di Toko diberi kode ambil 6 digit setelah disiapkan cabang, berlaku 48 jam. Lewat batas waktu tanpa diambil, pesanan dibatalkan otomatis dan stok dikembalikan.',
    },
    {
        title: '7. Kebijakan Retur Obat',
        body: 'Karena menyangkut keamanan konsumsi, obat yang sudah diserahkan ke pelanggan (baik diantar maupun diambil) tidak dapat dikembalikan atau ditukar, kecuali: (a) produk yang diterima salah atau berbeda dari yang dipesan, (b) produk rusak atau kedaluwarsa saat diterima, atau (c) segel kemasan sudah rusak sebelum diterima pelanggan. Klaim retur harus diajukan dalam 2x24 jam sejak barang diterima, dengan foto kondisi produk, melalui kontak cabang tempat pesanan dibuat. Retur yang disetujui diproses sebagai pengembalian dana, bukan penukaran barang — lihat Kebijakan Pengembalian Dana.',
    },
    {
        title: '8. Golongan Obat dan Batasan',
        body: 'Setiap produk menampilkan golongan obatnya (bebas atau bebas terbatas) beserta peringatan P1-P6 bila berlaku. Sebagian produk punya batas jumlah pembelian per transaksi, ditampilkan di halaman produk. Informasi pada aplikasi ini adalah informasi umum dan bukan pengganti nasihat medis dari apoteker atau dokter — lihat "Cara Menghubungi Apoteker" pada halaman produk.',
    },
    {
        title: '9. Perubahan Ketentuan',
        body: 'Kami dapat memperbarui Syarat & Ketentuan ini sewaktu-waktu. Perubahan berlaku sejak dipublikasikan di halaman ini.',
    },
];

export default function Terms() {
    return (
        <MobileLayout
            title="Syarat & Ketentuan"
            header={<AppBar title="Syarat & Ketentuan" back="/ui/profile" tone="brand" />}
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
