import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import BarChart from '@/Components/Admin/BarChart';
import Card from '@/Components/Admin/Card';
import StatCard from '@/Components/Admin/StatCard';
import Table from '@/Components/Admin/Table';
import { money, orders, products, revenueSeries, statusTone } from '@/Components/Admin/data';

const stats = [
    { label: 'Penjualan Hari Ini', value: money(12480000), icon: 'solar:cart-5-bold-duotone', change: '+4,2%', up: true, period: 'Kemarin' },
    { label: 'Pesanan Hari Ini', value: '148', icon: 'solar:bag-smile-bold-duotone', change: '+1,8%', up: true, period: 'Kemarin' },
    { label: 'Rata-rata Nilai Order', value: money(84300), icon: 'solar:tag-price-broken', change: '-0,6%', up: false, period: 'Minggu lalu' },
    { label: 'Tingkat Konversi', value: '3,7%', icon: 'solar:chat-square-like-bold-duotone', change: '+0,4%', up: true, period: 'Minggu lalu' },
];

const channels = [
    { name: 'Aplikasi Mobile', share: 46, revenue: 386400000 },
    { name: 'Situs Web', share: 34, revenue: 285600000 },
    { name: 'Marketplace', share: 14, revenue: 117600000 },
    { name: 'Toko Fisik', share: 6, revenue: 50400000 },
];

export default function DashboardSales() {
    return (
        <AdminLayout
            title="Dasbor Penjualan"
            heading="Dasbor Penjualan"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Dasbor', href: '/admin' },
                { label: 'Penjualan' },
            ]}
        >
            <div className="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {stats.map((stat) => (
                    <StatCard key={stat.label} {...stat} />
                ))}
            </div>

            <div className="mb-5 grid gap-4 lg:grid-cols-3">
                <Card title="Penjualan Mingguan" className="lg:col-span-2">
                    <BarChart series={revenueSeries} />
                </Card>

                <Card title="Kanal Penjualan">
                    <ul className="space-y-4">
                        {channels.map((channel) => (
                            <li key={channel.name}>
                                <div className="mb-1.5 flex items-baseline justify-between gap-2">
                                    <span className="text-[13px] font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {channel.name}
                                    </span>
                                    <span className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                        {channel.share}%
                                    </span>
                                </div>

                                <div className="h-2 overflow-hidden rounded-full bg-admin-hover dark:bg-admin-dark-hover">
                                    <div
                                        style={{ width: `${channel.share}%` }}
                                        className="h-full rounded-full bg-brand"
                                    />
                                </div>

                                <p className="mt-1 text-xs text-admin-muted dark:text-admin-dark-muted">
                                    {money(channel.revenue)}
                                </p>
                            </li>
                        ))}
                    </ul>
                </Card>
            </div>

            <div className="grid gap-4 lg:grid-cols-2">
                <Card title="Produk Terlaris" action={{ label: 'Semua', href: '/admin/produk' }} bodyClassName="p-0">
                    <Table
                        columns={[
                            { key: 'name', label: 'Produk' },
                            { key: 'sold', label: 'Terjual', align: 'right' },
                            { key: 'price', label: 'Harga', align: 'right' },
                        ]}
                        rows={products.slice(0, 5)}
                        rowKey={(row) => row.id}
                        renderCell={(row, key) => {
                            if (key === 'name') {
                                return (
                                    <span className="flex items-center gap-2.5">
                                        <img
                                            src={row.image}
                                            alt=""
                                            className="h-8 w-8 shrink-0 rounded-lg bg-admin-hover object-contain p-1 dark:bg-admin-dark-hover"
                                        />
                                        <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                            {row.name}
                                        </span>
                                    </span>
                                );
                            }

                            if (key === 'price') {
                                return (
                                    <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                        {money(row.price)}
                                    </span>
                                );
                            }

                            return row[key];
                        }}
                    />
                </Card>

                <Card title="Pesanan Terbaru" action={{ label: 'Semua', href: '/admin/pesanan' }} bodyClassName="p-0">
                    <Table
                        columns={[
                            { key: 'id', label: 'Pesanan' },
                            { key: 'customer', label: 'Pelanggan' },
                            { key: 'total', label: 'Total', align: 'right' },
                            { key: 'status', label: 'Status' },
                        ]}
                        rows={orders}
                        rowKey={(row) => row.id}
                        renderCell={(row, key) => {
                            if (key === 'total') {
                                return (
                                    <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                        {money(row.total)}
                                    </span>
                                );
                            }

                            if (key === 'status') {
                                return <Badge tone={statusTone(row.status)}>{row.status}</Badge>;
                            }

                            return row[key];
                        }}
                    />
                </Card>
            </div>
        </AdminLayout>
    );
}
