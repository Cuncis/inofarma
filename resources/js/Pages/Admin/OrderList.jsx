import { useState } from 'react';
import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import RowActions from '@/Components/Admin/RowActions';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';
import { money, orders as seed, statusTone } from '@/Components/Admin/data';

const columns = [
    { key: 'id', label: 'No. Pesanan' },
    { key: 'customer', label: 'Pelanggan' },
    { key: 'date', label: 'Tanggal' },
    { key: 'payment', label: 'Pembayaran' },
    { key: 'total', label: 'Total', align: 'right' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

const statuses = ['Semua Status', 'Selesai', 'Diproses', 'Dikirim', 'Dibatalkan'];

export default function OrderList() {
    const [rows, setRows] = useState(seed);
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('Semua Status');

    const visible = rows.filter((row) => {
        const matchesSearch =
            row.customer.toLowerCase().includes(search.toLowerCase()) ||
            row.id.toLowerCase().includes(search.toLowerCase());

        return matchesSearch && (status === 'Semua Status' || row.status === status);
    });

    return (
        <AdminLayout
            title="Daftar Pesanan"
            heading="Pesanan"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Pesanan' }]}
            actions={
                <Button variant="outline" size="sm" icon="solar:download-minimalistic-broken">
                    Ekspor
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar
                    search={search}
                    onSearch={setSearch}
                    placeholder="Cari nomor pesanan atau pelanggan..."
                    filter={{ value: status, onChange: setStatus, options: statuses }}
                />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.id}
                    empty="Pesanan tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'id') {
                            return (
                                <Link
                                    href="/admin/pesanan/detail"
                                    className="font-semibold text-brand hover:underline"
                                >
                                    {row.id}
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

                        if (key === 'actions') {
                            return (
                                <RowActions
                                    label={row.id}
                                    viewHref="/admin/pesanan/detail"
                                    onDelete={() =>
                                        setRows((current) =>
                                            current.filter((item) => item.id !== row.id),
                                        )
                                    }
                                />
                            );
                        }

                        return row[key];
                    }}
                />
            </Card>
        </AdminLayout>
    );
}
