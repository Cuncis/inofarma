import { useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { moneyShort } from '@/lib/format';
import BarChart from '@/Components/Admin/BarChart';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import PeriodFilter from '@/Components/Admin/PeriodFilter';
import StatCard from '@/Components/Admin/StatCard';
import Table from '@/Components/Admin/Table';
import {
    dashboardStats,
    money,
    orders,
    products,
    revenueByPeriod,
    revenuePeriods,
    revenueTotal,
    statusTone,
} from '@/Components/Admin/data';

const periodOptions = revenuePeriods.map((key) => ({
    value: key,
    label: revenueByPeriod[key].label,
}));

const orderColumns = [
    { key: 'id', label: 'Pesanan' },
    { key: 'customer', label: 'Pelanggan' },
    { key: 'date', label: 'Tanggal' },
    { key: 'total', label: 'Total', align: 'right' },
    { key: 'status', label: 'Status' },
];

export default function Dashboard() {
    const [period, setPeriod] = useState('mingguan');

    const revenue = revenueByPeriod[period];

    // The revenue tile reports the same slice the chart plots, so the two can
    // never show different numbers for the selected period.
    const stats = useMemo(
        () =>
            dashboardStats.map((stat) =>
                stat.label === 'Pendapatan'
                    ? {
                          ...stat,
                          label: `Pendapatan (${revenue.label})`,
                          value: moneyShort(revenueTotal(period)),
                      }
                    : stat,
            ),
        [period, revenue.label],
    );

    return (
        <AdminLayout
            title="Dasbor"
            heading="Dasbor"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Dasbor' }]}
            actions={
                <>
                    <PeriodFilter
                        value={period}
                        onChange={setPeriod}
                        options={periodOptions}
                        label="Periode pendapatan"
                    />

                    <Button icon="solar:download-minimalistic-broken" variant="outline" size="sm">
                        Unduh Laporan
                    </Button>
                </>
            }
        >
            <div className="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {stats.map((stat) => (
                    <StatCard key={stat.label} {...stat} />
                ))}
            </div>

            <div className="mb-5 grid gap-4 lg:grid-cols-3">
                <Card
                    title={revenue.title}
                    action={{ label: 'Lihat detail', href: '/admin/faktur' }}
                    className="lg:col-span-2"
                >
                    <p className="-mt-1 mb-4 text-xs text-admin-muted dark:text-admin-dark-muted">
                        {revenue.caption} · total {money(revenueTotal(period))}
                    </p>

                    <BarChart series={revenue.series} />
                </Card>

                <Card title="Produk Terlaris" action={{ label: 'Semua', href: '/admin/produk' }}>
                    <ul className="space-y-4">
                        {products.slice(0, 5).map((product) => (
                            <li key={product.id} className="flex items-center gap-3">
                                <img
                                    src={product.image}
                                    alt=""
                                    className="h-10 w-10 shrink-0 rounded-lg bg-admin-hover object-contain p-1 dark:bg-admin-dark-hover"
                                />

                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-[13px] font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {product.name}
                                    </p>
                                    <p className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                        {product.sold} terjual
                                    </p>
                                </div>

                                <span className="shrink-0 text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {money(product.price)}
                                </span>
                            </li>
                        ))}
                    </ul>
                </Card>
            </div>

            <Card
                title="Pesanan Terbaru"
                action={{ label: 'Lihat semua', href: '/admin/pesanan' }}
                bodyClassName="p-0"
            >
                <Table
                    columns={orderColumns}
                    rows={orders}
                    rowKey={(row) => row.id}
                    renderCell={(row, key) => {
                        if (key === 'id') {
                            return (
                                <Link
                                    href={`/admin/pesanan/${row.id}`}
                                    className="font-semibold text-brand hover:underline"
                                >
                                    #{row.id}
                                </Link>
                            );
                        }

                        if (key === 'customer') {
                            return (
                                <span className="flex items-center gap-2.5">
                                    <img
                                        src={row.avatar}
                                        alt=""
                                        className="h-8 w-8 rounded-full object-cover"
                                    />
                                    <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {row.customer}
                                    </span>
                                </span>
                            );
                        }

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
        </AdminLayout>
    );
}
