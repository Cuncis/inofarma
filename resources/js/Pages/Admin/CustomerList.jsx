import { useState } from 'react';
import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import RowActions from '@/Components/Admin/RowActions';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';
import { customers as seed, money, statusTone } from '@/Components/Admin/data';

const columns = [
    { key: 'name', label: 'Pelanggan' },
    { key: 'phone', label: 'Telepon' },
    { key: 'city', label: 'Kota' },
    { key: 'orders', label: 'Pesanan', align: 'right' },
    { key: 'spent', label: 'Total Belanja', align: 'right' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

export default function CustomerList() {
    const [rows, setRows] = useState(seed);
    const [search, setSearch] = useState('');

    const visible = rows.filter(
        (row) =>
            row.name.toLowerCase().includes(search.toLowerCase()) ||
            row.email.toLowerCase().includes(search.toLowerCase()),
    );

    return (
        <AdminLayout
            title="Daftar Pelanggan"
            heading="Pelanggan"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Pelanggan' }]}
            actions={
                <Button href="/admin/pelanggan/tambah" icon="solar:add-circle-broken" size="sm">
                    Tambah Pelanggan
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar
                    search={search}
                    onSearch={setSearch}
                    placeholder="Cari nama atau email..."
                />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.email}
                    empty="Pelanggan tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'name') {
                            return (
                                <Link
                                    href="/admin/pelanggan/detail"
                                    className="flex items-center gap-2.5"
                                >
                                    <img
                                        src={row.avatar}
                                        alt=""
                                        className="h-9 w-9 rounded-full object-cover"
                                    />
                                    <span>
                                        <span className="block font-medium text-admin-heading dark:text-admin-dark-heading">
                                            {row.name}
                                        </span>
                                        <span className="block text-xs text-admin-muted dark:text-admin-dark-muted">
                                            {row.email}
                                        </span>
                                    </span>
                                </Link>
                            );
                        }

                        if (key === 'spent') {
                            return (
                                <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {money(row.spent)}
                                </span>
                            );
                        }

                        if (key === 'status') {
                            return <Badge tone={statusTone(row.status)}>{row.status}</Badge>;
                        }

                        if (key === 'actions') {
                            return (
                                <RowActions
                                    label={row.name}
                                    viewHref="/admin/pelanggan/detail"
                                    editHref="/admin/pelanggan/ubah"
                                    onDelete={() =>
                                        setRows((current) =>
                                            current.filter((item) => item.email !== row.email),
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
