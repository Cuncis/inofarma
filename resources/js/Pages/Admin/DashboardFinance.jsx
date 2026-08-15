import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import BarChart from '@/Components/Admin/BarChart';
import Card from '@/Components/Admin/Card';
import StatCard from '@/Components/Admin/StatCard';
import Table from '@/Components/Admin/Table';
import { invoices, money, statusTone } from '@/Components/Admin/data';

const stats = [
    { label: 'Pendapatan Bulan Ini', value: money(1236800000), icon: 'solar:wallet-money-bold-duotone', change: '+10,6%', up: true, period: 'Bulan lalu' },
    { label: 'Laba Kotor', value: money(412300000), icon: 'solar:bill-list-bold-duotone', change: '+7,4%', up: true, period: 'Bulan lalu' },
    { label: 'Beban Operasional', value: money(184500000), icon: 'solar:card-send-bold-duotone', change: '+2,1%', up: false, period: 'Bulan lalu' },
    { label: 'Piutang Belum Tertagih', value: money(96400000), icon: 'solar:clock-circle-bold-duotone', change: '-3,8%', up: true, period: 'Bulan lalu' },
];

const cashflow = [
    { label: 'Mar', value: 62 },
    { label: 'Apr', value: 71 },
    { label: 'Mei', value: 58 },
    { label: 'Jun', value: 84 },
    { label: 'Jul', value: 76 },
    { label: 'Agu', value: 92 },
];

const expenses = [
    { name: 'Pembelian Stok', amount: 96400000, share: 52 },
    { name: 'Gaji Karyawan', amount: 48200000, share: 26 },
    { name: 'Sewa & Utilitas', amount: 22100000, share: 12 },
    { name: 'Pemasaran', amount: 12900000, share: 7 },
    { name: 'Lain-lain', amount: 4900000, share: 3 },
];

export default function DashboardFinance() {
    return (
        <AdminLayout
            title="Dasbor Keuangan"
            heading="Dasbor Keuangan"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Dasbor', href: '/admin' },
                { label: 'Keuangan' },
            ]}
        >
            <div className="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {stats.map((stat) => (
                    <StatCard key={stat.label} {...stat} />
                ))}
            </div>

            <div className="mb-5 grid gap-4 lg:grid-cols-3">
                <Card title="Arus Kas 6 Bulan" className="lg:col-span-2">
                    <BarChart series={cashflow} />
                </Card>

                <Card title="Rincian Beban">
                    <ul className="space-y-4">
                        {expenses.map((expense) => (
                            <li key={expense.name}>
                                <div className="mb-1.5 flex items-baseline justify-between gap-2">
                                    <span className="text-[13px] font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {expense.name}
                                    </span>
                                    <span className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                        {expense.share}%
                                    </span>
                                </div>

                                <div className="h-2 overflow-hidden rounded-full bg-admin-hover dark:bg-admin-dark-hover">
                                    <div
                                        style={{ width: `${expense.share}%` }}
                                        className="h-full rounded-full bg-warning"
                                    />
                                </div>

                                <p className="mt-1 text-xs text-admin-muted dark:text-admin-dark-muted">
                                    {money(expense.amount)}
                                </p>
                            </li>
                        ))}
                    </ul>
                </Card>
            </div>

            <Card title="Faktur Terbaru" action={{ label: 'Semua faktur', href: '/admin/faktur' }} bodyClassName="p-0">
                <Table
                    columns={[
                        { key: 'number', label: 'No. Faktur' },
                        { key: 'customer', label: 'Pelanggan' },
                        { key: 'issued', label: 'Diterbitkan' },
                        { key: 'due', label: 'Jatuh Tempo' },
                        { key: 'total', label: 'Total', align: 'right' },
                        { key: 'status', label: 'Status' },
                    ]}
                    rows={invoices}
                    rowKey={(row) => row.number}
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
        </AdminLayout>
    );
}
