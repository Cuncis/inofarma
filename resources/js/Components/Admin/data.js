import { money } from '@/lib/format';
import { media } from '@/lib/media';

/**
 * Admin demo fixtures.
 *
 * Static stand-ins so every screen renders with believable content, for the
 * screens whose feature does not exist yet (invoices, purchasing, coupons).
 *
 * Products and categories are NOT here any more — they come from the database
 * through the shared `catalog` prop. Anything below that duplicates a real
 * table is a drift risk and should go the same way as its screen gets built.
 */

export const img = media;

/** Dashboard summary tiles. */
export const dashboardStats = [
    {
        label: 'Total Pesanan',
        value: '13.647',
        icon: 'solar:cart-5-bold-duotone',
        change: '+2,3%',
        up: true,
        period: 'Minggu lalu',
    },
    {
        label: 'Pelanggan Baru',
        value: '9.526',
        icon: 'solar:users-group-two-rounded-bold-duotone',
        change: '+8,1%',
        up: true,
        period: 'Bulan lalu',
    },
    {
        label: 'Transaksi',
        value: '976',
        icon: 'solar:bag-smile-bold-duotone',
        change: '-0,3%',
        up: false,
        period: 'Minggu lalu',
    },
    {
        label: 'Pendapatan',
        value: money(1236800000),
        icon: 'solar:wallet-money-bold-duotone',
        change: '+10,6%',
        up: true,
        period: 'Bulan lalu',
    },
];

/**
 * Revenue by reporting period, in rupiah.
 *
 * Each period supplies its own x-axis granularity: hours within today, days
 * within the week, weeks within the month, months within the year. `total` is
 * the sum the summary tile shows for that period, so the tile and the chart can
 * never disagree.
 *
 * @type {Record<string, { label: string, title: string, caption: string, series: { label: string, value: number }[] }>}
 */
export const revenueByPeriod = {
    harian: {
        label: 'Harian',
        title: 'Pendapatan Harian',
        caption: 'Hari ini, per jam',
        series: [
            { label: '08.00', value: 3200000 },
            { label: '10.00', value: 6800000 },
            { label: '12.00', value: 8400000 },
            { label: '14.00', value: 7100000 },
            { label: '16.00', value: 9600000 },
            { label: '18.00', value: 6900000 },
            { label: '20.00', value: 3800000 },
        ],
    },
    mingguan: {
        label: 'Mingguan',
        title: 'Pendapatan Mingguan',
        caption: 'Minggu ini, per hari',
        series: [
            { label: 'Sen', value: 30400000 },
            { label: 'Sel', value: 36200000 },
            { label: 'Rab', value: 33100000 },
            { label: 'Kam', value: 45800000 },
            { label: 'Jum', value: 41200000 },
            { label: 'Sab', value: 54600000 },
            { label: 'Min', value: 46500000 },
        ],
    },
    bulanan: {
        label: 'Bulanan',
        title: 'Pendapatan Bulanan',
        caption: 'Bulan ini, per minggu',
        series: [
            { label: 'Mgg 1', value: 268000000 },
            { label: 'Mgg 2', value: 312000000 },
            { label: 'Mgg 3', value: 287000000 },
            { label: 'Mgg 4', value: 241000000 },
            { label: 'Mgg 5', value: 128000000 },
        ],
    },
    tahunan: {
        label: 'Tahunan',
        title: 'Pendapatan Tahunan',
        caption: 'Tahun ini, per bulan',
        series: [
            { label: 'Jan', value: 892000000 },
            { label: 'Feb', value: 845000000 },
            { label: 'Mar', value: 984000000 },
            { label: 'Apr', value: 1120000000 },
            { label: 'Mei', value: 921000000 },
            { label: 'Jun', value: 1340000000 },
            { label: 'Jul', value: 1210000000 },
            { label: 'Agu', value: 1400000000 },
            { label: 'Sep', value: 1180000000 },
            { label: 'Okt', value: 1265000000 },
            { label: 'Nov', value: 1390000000 },
            { label: 'Des', value: 1520000000 },
        ],
    },
};

/** Period keys in the order the filter presents them. */
export const revenuePeriods = ['harian', 'mingguan', 'bulanan', 'tahunan'];

/**
 * Total revenue for a period, summed from the same series the chart plots.
 *
 * @param {string} period
 * @returns {number}
 */
export function revenueTotal(period) {
    return revenueByPeriod[period].series.reduce((sum, point) => sum + point.value, 0);
}

/**
 * Order fixtures kept in step with `Database\Seeders\OrderSeeder`.
 *
 * The order screens read the database; this list only feeds the global search
 * index and the dashboard tables, so ids must match (no `#` prefix — the hash
 * is added at render time, it is not part of the identifier).
 */
export const orders = [
    { id: 'INO-2451', customer: 'Kirana Wijaya', avatar: img.user(1), date: '14 Agu 2025', total: 482000, payment: 'Transfer Bank', status: 'Selesai' },
    { id: 'INO-2450', customer: 'Rizky Ananda', avatar: img.user(2), date: '14 Agu 2025', total: 1251000, payment: 'GoPay', status: 'Diproses' },
    { id: 'INO-2449', customer: 'Dinda Puspita', avatar: img.user(3), date: '13 Agu 2025', total: 264000, payment: 'OVO', status: 'Dikirim' },
    { id: 'INO-2448', customer: 'Bagas Saputra', avatar: img.user(4), date: '13 Agu 2025', total: 89500, payment: 'DANA', status: 'Dibatalkan' },
    { id: 'INO-2447', customer: 'Sari Wulandari', avatar: img.user(5), date: '12 Agu 2025', total: 630000, payment: 'Transfer Bank', status: 'Selesai' },
];

export const customers = [
    { id: 'CUS-001', name: 'Kirana Wijaya', email: 'kirana.wijaya@mail.com', avatar: img.user(1), phone: '+62 812-3456-7890', city: 'Jakarta Barat', status: 'Aktif' },
    { id: 'CUS-002', name: 'Rizky Ananda', email: 'rizky.ananda@mail.com', avatar: img.user(2), phone: '+62 813-2233-4455', city: 'Bandung', status: 'Aktif' },
    { id: 'CUS-003', name: 'Dinda Puspita', email: 'dinda.puspita@mail.com', avatar: img.user(3), phone: '+62 856-7788-9900', city: 'Surabaya', status: 'Aktif' },
    { id: 'CUS-004', name: 'Bagas Saputra', email: 'bagas.saputra@mail.com', avatar: img.user(4), phone: '+62 878-1122-3344', city: 'Yogyakarta', status: 'Nonaktif' },
    { id: 'CUS-005', name: 'Sari Wulandari', email: 'sari.wulandari@mail.com', avatar: img.user(5), phone: '+62 811-5566-7788', city: 'Semarang', status: 'Aktif' },
    { id: 'CUS-006', name: 'Anisa Rahmawati', email: 'anisa.rahmawati@mail.com', avatar: img.user(6), phone: '+62 852-9900-1122', city: 'Medan', status: 'Aktif' },
];

/**
 * Seller fixtures kept in step with `Database\Seeders\CatalogSeeder`.
 *
 * The seller screens read the database; this list only feeds the global search
 * index, so the ids must match or search would link nowhere.
 */
export const sellers = [
    { id: 'SEL-001', name: 'Apotek Sehat Bersama', owner: 'Kirana Wijaya', logo: img.seller('nike'), city: 'Jakarta Selatan', status: 'Aktif' },
    { id: 'SEL-002', name: 'Toko Obat Mandiri', owner: 'Rizky Ananda', logo: img.seller('dyson'), city: 'Bandung', status: 'Aktif' },
    { id: 'SEL-003', name: 'Farmasi Nusantara', owner: 'Dinda Puspita', logo: img.seller('huawei'), city: 'Surabaya', status: 'Aktif' },
    { id: 'SEL-004', name: 'Griya Farma', owner: 'Bagas Saputra', logo: img.seller('gopro'), city: 'Yogyakarta', status: 'Aktif' },
    { id: 'SEL-005', name: 'Apotek Melati', owner: 'Anisa Rahmawati', logo: img.seller('zara'), city: 'Medan', status: 'Nonaktif' },
];

/**
 * Branch fixtures kept in step with `Database\Seeders\BranchSeeder`.
 *
 * The branch screens read the database; this list only feeds the global
 * search index, so the ids must match or search would link nowhere.
 */
export const branches = [
    { id: 'CB-001', name: 'Apotek Inofarma Kapten Yusuf', kota: 'Kota Bogor' },
    { id: 'CB-002', name: 'Apotek Inofarma Otista', kota: 'Kota Tangerang Selatan' },
    { id: 'CB-003', name: 'Apotek Inofarma Darul Fallah', kota: 'Jakarta Selatan' },
    { id: 'CB-004', name: 'Apotek Inofarma Parakan', kota: 'Kota Tangerang Selatan' },
    { id: 'CB-005', name: 'Apotek Inofarma Syahdan', kota: 'Jakarta Barat' },
    { id: 'CB-006', name: 'Apotek Inofarma Taruna Jaya', kota: 'Jakarta Timur' },
    { id: 'CB-007', name: 'Apotek Inofarma Keamanan', kota: 'Jakarta Barat' },
    { id: 'CB-008', name: 'Apotek Inofarma Pulo Gebang', kota: 'Jakarta Timur' },
    { id: 'CB-009', name: 'Apotek Inofarma Duri Kepa', kota: 'Jakarta Barat' },
    { id: 'CB-010', name: 'Apotek Inofarma Kebagusan', kota: 'Jakarta Selatan' },
];


/**
 * Map a fixture status word onto a Badge tone.
 *
 * @param {string} status
 * @returns {string}
 */
export function statusTone(status) {
    const map = {
        Aktif: 'success',
        Selesai: 'success',
        Terverifikasi: 'success',
        Diproses: 'warning',
        Menunggu: 'warning',
        'Stok Menipis': 'warning',
        Tersedia: 'success',
        Dikirim: 'info',
        'Siap Diambil': 'info',
        Dibatalkan: 'danger',
        Kedaluwarsa: 'danger',
        Habis: 'danger',
        Nonaktif: 'neutral',
        Arsip: 'neutral',
        Lunas: 'success',
        Diterima: 'success',
        Disetujui: 'success',
        'Belum Bayar': 'warning',
        Sebagian: 'warning',
        'Hampir Penuh': 'warning',
        'Jatuh Tempo': 'danger',
        'Menunggu Pembayaran': 'warning',
        Refund: 'info',

        // Payment gateway attempt statuses (Fase 6, `payments.status`).
        Success: 'success',
        Pending: 'warning',
        Failed: 'danger',
        Expired: 'danger',
        Refunded: 'info',
    };

    return map[status] ?? 'neutral';
}

export { money };

// `attributes` and `coupons` fixtures are gone — Atribut and Kupon are real
// entities as of Fase 4.3, backed by `AttributeController`/`CouponController`.
// `invoices`/`invoiceLines` stay: the real Faktur screens read `Order` data
// directly (see `InvoicePresenter`), and only `DashboardFinance`'s decorative
// summary table still uses this fixture.

export const invoices = [
    { number: 'INV-2025-0451', customer: 'Kirana Wijaya', issued: '14 Agu 2025', due: '21 Agu 2025', total: 486000, status: 'Lunas' },
    { number: 'INV-2025-0450', customer: 'Rizky Ananda', issued: '14 Agu 2025', due: '21 Agu 2025', total: 1250000, status: 'Belum Bayar' },
    { number: 'INV-2025-0449', customer: 'Dinda Puspita', issued: '13 Agu 2025', due: '20 Agu 2025', total: 275000, status: 'Lunas' },
    { number: 'INV-2025-0448', customer: 'Bagas Saputra', issued: '12 Agu 2025', due: '19 Agu 2025', total: 92000, status: 'Jatuh Tempo' },
];

export const invoiceLines = [
    { name: 'Paracetamol 500mg', qty: 12, price: 12500 },
    { name: 'Vitamin C 1000mg', qty: 3, price: 75000 },
    { name: 'Masker Medis 3 Ply', qty: 2, price: 45000 },
];

export const purchases = [
    { number: 'PO-1043', supplier: 'PT Kimia Farma', date: '14 Agu 2025', items: 12, total: 18400000, status: 'Diterima' },
    { number: 'PO-1042', supplier: 'PT Kalbe Farma', date: '11 Agu 2025', items: 8, total: 9600000, status: 'Dikirim' },
    { number: 'PO-1041', supplier: 'PT Dexa Medica', date: '08 Agu 2025', items: 21, total: 27350000, status: 'Menunggu' },
    { number: 'PO-1040', supplier: 'PT Bio Farma', date: '05 Agu 2025', items: 5, total: 4250000, status: 'Dibatalkan' },
];

export const purchaseReturns = [
    { number: 'RTN-0231', supplier: 'PT Kimia Farma', date: '13 Agu 2025', items: 3, total: 640000, reason: 'Kemasan rusak', status: 'Disetujui' },
    { number: 'RTN-0230', supplier: 'PT Kalbe Farma', date: '10 Agu 2025', items: 1, total: 125000, reason: 'Mendekati kedaluwarsa', status: 'Diproses' },
    { number: 'RTN-0229', supplier: 'PT Dexa Medica', date: '06 Agu 2025', items: 6, total: 1830000, reason: 'Salah kirim', status: 'Disetujui' },
];

export const productReviews = [
    { author: 'Sari Wulandari', avatar: img.user(5), product: 'Paracetamol 500mg', score: 5, date: '14 Agu 2025', body: 'Pengiriman cepat dan obat asli. Kemasan rapi, akan beli lagi.', status: 'Disetujui' },
    { author: 'Rizky Ananda', avatar: img.user(2), product: 'Vitamin C 1000mg', score: 4, date: '13 Agu 2025', body: 'Kualitas bagus, tapi harganya sedikit lebih mahal dari apotek sebelah.', status: 'Disetujui' },
    { author: 'Dinda Puspita', avatar: img.user(3), product: 'Masker Medis 3 Ply', score: 5, date: '12 Agu 2025', body: 'Maskernya nyaman dipakai seharian, tidak bikin sesak.', status: 'Menunggu' },
    { author: 'Bagas Saputra', avatar: img.user(4), product: 'Hand Sanitizer 500ml', score: 3, date: '10 Agu 2025', body: 'Aromanya agak menyengat, tapi efektif dan cepat kering.', status: 'Menunggu' },
];

export const adminFaqs = [
    { question: 'Bagaimana cara menambahkan produk baru?', answer: 'Buka menu Produk lalu klik tombol Tambah Produk. Lengkapi nama, kategori, harga, dan stok, kemudian simpan.' },
    { question: 'Bagaimana cara mengatur hak akses pengguna?', answer: 'Buka menu Hak Akses untuk mengatur izin per peran, atau menu Peran untuk membuat peran baru beserta izinnya.' },
    { question: 'Apakah stok otomatis berkurang saat ada pesanan?', answer: 'Ya. Setiap pesanan yang diproses akan mengurangi stok pada gudang yang dipilih secara otomatis.' },
    { question: 'Bagaimana cara mencetak faktur?', answer: 'Buka detail faktur yang diinginkan, lalu klik tombol Cetak di bagian kanan atas halaman.' },
    { question: 'Bisakah saya mengekspor laporan penjualan?', answer: 'Bisa. Pada halaman Pesanan dan Dasbor tersedia tombol Ekspor untuk mengunduh laporan.' },
];

export const helpTopics = [
    { title: 'Memulai', icon: 'solar:widget-5-bold-duotone', count: 12, description: 'Panduan dasar penggunaan panel admin Inofarma.' },
    { title: 'Produk & Stok', icon: 'solar:box-bold-duotone', count: 18, description: 'Kelola katalog, kategori, atribut, dan inventaris.' },
    { title: 'Pesanan', icon: 'solar:bag-smile-bold-duotone', count: 15, description: 'Proses, kirim, dan selesaikan pesanan pelanggan.' },
    { title: 'Pembayaran', icon: 'solar:card-send-bold-duotone', count: 9, description: 'Metode pembayaran, faktur, dan pengembalian dana.' },
    { title: 'Pengguna & Akses', icon: 'solar:users-group-two-rounded-bold-duotone', count: 7, description: 'Atur peran, izin, dan akun staf apotek.' },
    { title: 'Keamanan', icon: 'solar:lock-keyhole-bold-duotone', count: 6, description: 'Kata sandi, verifikasi dua langkah, dan log aktivitas.' },
];

export const pricingPlans = [
    { name: 'Dasar', price: 0, period: 'bulan', highlight: false, features: ['1 gudang', '100 produk', 'Laporan dasar', 'Dukungan email'] },
    { name: 'Profesional', price: 499000, period: 'bulan', highlight: true, features: ['5 gudang', 'Produk tanpa batas', 'Laporan lengkap', 'Dukungan prioritas', 'Multi-pengguna'] },
    { name: 'Enterprise', price: 1499000, period: 'bulan', highlight: false, features: ['Gudang tanpa batas', 'Produk tanpa batas', 'Integrasi API', 'Manajer akun khusus', 'SLA 99,9%'] },
];

export const activityTimeline = [
    { date: '14 Agu 2025', items: [
        { time: '09.00', title: 'Pesanan #INO-2451 dibuat', body: 'Kirana Wijaya memesan 3 item senilai Rp 486.000.', icon: 'solar:bag-smile-bold-duotone', tone: 'brand' },
        { time: '11.30', title: 'Stok Paracetamol 500mg ditambah', body: 'Penerimaan PO-1043 sebanyak 120 strip ke Gudang Jakarta.', icon: 'solar:box-bold-duotone', tone: 'success' },
    ] },
    { date: '13 Agu 2025', items: [
        { time: '14.20', title: 'Ulasan baru menunggu moderasi', body: 'Dinda Puspita memberi ulasan pada Masker Medis 3 Ply.', icon: 'solar:chat-square-like-bold-duotone', tone: 'warning' },
        { time: '16.45', title: 'Pesanan #INO-2448 dibatalkan', body: 'Pembatalan diajukan oleh pelanggan sebelum pengiriman.', icon: 'solar:danger-triangle-broken', tone: 'danger' },
    ] },
    { date: '12 Agu 2025', items: [
        { time: '08.10', title: 'Penjual baru terdaftar', body: 'Griya Farma mendaftar dan menunggu verifikasi izin.', icon: 'solar:shop-bold-duotone', tone: 'info' },
    ] },
];

export const conversations = [
    { name: 'Kirana Wijaya', avatar: img.user(1), last: 'Baik, saya tunggu konfirmasinya ya.', at: '09.42', unread: 2, online: true },
    { name: 'Rizky Ananda', avatar: img.user(2), last: 'Stok Amoxicillin masih ada?', at: '09.15', unread: 0, online: true },
    { name: 'Dinda Puspita', avatar: img.user(3), last: 'Terima kasih bantuannya!', at: 'Kemarin', unread: 0, online: false },
    { name: 'Bagas Saputra', avatar: img.user(4), last: 'Pesanan saya kok belum dikirim?', at: 'Kemarin', unread: 1, online: false },
    { name: 'Sari Wulandari', avatar: img.user(5), last: 'Apakah bisa kirim ke Semarang?', at: '2 hari lalu', unread: 0, online: false },
];

export const chatMessages = [
    { from: 'them', body: 'Selamat pagi, saya mau tanya soal pesanan #INO-2451.', at: '09.30' },
    { from: 'me', body: 'Selamat pagi Bu Kirana. Silakan, ada yang bisa kami bantu?', at: '09.32' },
    { from: 'them', body: 'Kira-kira sampai kapan ya pesanan saya dikirim?', at: '09.35' },
    { from: 'me', body: 'Pesanan sedang disiapkan dan akan dikirim hari ini. Estimasi tiba besok sore.', at: '09.38' },
    { from: 'them', body: 'Baik, saya tunggu konfirmasinya ya.', at: '09.42' },
];

export const emails = [
    { from: 'PT Kimia Farma', subject: 'Konfirmasi pengiriman PO-1043', preview: 'Kami informasikan bahwa pesanan Anda telah dikirim...', at: '09.20', unread: true, starred: true },
    { from: 'Rizky Ananda', subject: 'Pertanyaan stok Amoxicillin', preview: 'Halo, apakah Amoxicillin 500mg masih tersedia?', at: '08.55', unread: true, starred: false },
    { from: 'Sistem Inofarma', subject: 'Laporan penjualan mingguan', preview: 'Ringkasan penjualan 07-13 Agustus 2025 sudah siap...', at: 'Kemarin', unread: false, starred: true },
    { from: 'PT Kalbe Farma', subject: 'Penawaran harga produk baru', preview: 'Bersama ini kami sampaikan daftar harga terbaru...', at: 'Kemarin', unread: false, starred: false },
    { from: 'Dinda Puspita', subject: 'Terima kasih', preview: 'Pelayanannya sangat memuaskan, terima kasih!', at: '2 hari lalu', unread: false, starred: false },
];

export const calendarEvents = [
    { day: 5, title: 'Audit stok bulanan', tone: 'brand' },
    { day: 8, title: 'Kirim PO ke Kalbe', tone: 'warning' },
    { day: 14, title: 'Rapat tim apotek', tone: 'info' },
    { day: 18, title: 'Jatuh tempo INV-0450', tone: 'danger' },
    { day: 22, title: 'Pelatihan staf baru', tone: 'success' },
    { day: 27, title: 'Tutup buku bulanan', tone: 'brand' },
];

export const todoItems = [
    { id: 1, title: 'Verifikasi izin Griya Farma', due: 'Hari ini', priority: 'Tinggi', done: false },
    { id: 2, title: 'Cek stok menipis di Gudang Medan', due: 'Hari ini', priority: 'Tinggi', done: false },
    { id: 3, title: 'Balas ulasan pelanggan yang tertunda', due: 'Besok', priority: 'Sedang', done: false },
    { id: 4, title: 'Susun laporan penjualan Agustus', due: '20 Agu', priority: 'Sedang', done: false },
    { id: 5, title: 'Perbarui harga produk Kalbe', due: '18 Agu', priority: 'Rendah', done: true },
    { id: 6, title: 'Arsipkan faktur Juli', due: '15 Agu', priority: 'Rendah', done: true },
];
