import { money } from '@/lib/format';
import { media } from '@/lib/media';

/**
 * Admin demo fixtures.
 *
 * Static stand-ins backing the dashboard and the global search index — the
 * only pieces of the admin that still show fixture data instead of the
 * database directly.
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
