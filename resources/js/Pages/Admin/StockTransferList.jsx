import { useState } from 'react';
import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';
import { statusTone } from '@/Components/Admin/data';

const columns = [
    { key: 'id', label: 'Kode' },
    { key: 'productName', label: 'Produk' },
    { key: 'route', label: 'Dari → Ke' },
    { key: 'quantity', label: 'Jumlah', align: 'right' },
    { key: 'status', label: 'Status' },
    { key: 'requestedAt', label: 'Diminta' },
];

export default function StockTransferList({ transfers, statuses }) {
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('Semua Status');

    const visible = transfers.filter((transfer) => {
        const matchesStatus = status === 'Semua Status' || transfer.status === status;
        const matchesSearch =
            transfer.id.toLowerCase().includes(search.toLowerCase()) ||
            transfer.productName.toLowerCase().includes(search.toLowerCase());

        return matchesStatus && matchesSearch;
    });

    return (
        <AdminLayout
            title="Transfer Stok"
            heading="Transfer Stok"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Inventaris' },
                { label: 'Transfer Stok' },
            ]}
            actions={
                <Button
                    href="/admin/inventaris/transfer/tambah"
                    icon="solar:add-circle-broken"
                    size="sm"
                >
                    Buat Transfer
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar
                    search={search}
                    onSearch={setSearch}
                    placeholder="Cari kode atau produk..."
                    filter={{
                        value: status,
                        onChange: setStatus,
                        options: ['Semua Status', ...statuses],
                    }}
                />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.id}
                    empty="Belum ada transfer stok."
                    renderCell={(row, key) => {
                        if (key === 'id') {
                            return (
                                <Link
                                    href={`/admin/inventaris/transfer/${row.id}`}
                                    className="font-medium text-admin-heading underline-offset-2 hover:underline dark:text-admin-dark-heading"
                                >
                                    {row.id}
                                </Link>
                            );
                        }

                        if (key === 'route') {
                            return (
                                <span className="text-[13px]">
                                    {row.fromBranchName} → {row.toBranchName}
                                </span>
                            );
                        }

                        if (key === 'quantity') {
                            return (
                                <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {row.quantity}
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
