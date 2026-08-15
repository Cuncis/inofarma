import { useState } from 'react';
import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import RowActions from '@/Components/Admin/RowActions';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';
import { money, sellers as seed, statusTone } from '@/Components/Admin/data';

const columns = [
    { key: 'name', label: 'Penjual' },
    { key: 'owner', label: 'Pemilik' },
    { key: 'city', label: 'Kota' },
    { key: 'products', label: 'Produk', align: 'right' },
    { key: 'revenue', label: 'Pendapatan', align: 'right' },
    { key: 'rating', label: 'Rating', align: 'right' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

export default function SellerList() {
    const [rows, setRows] = useState(seed);
    const [search, setSearch] = useState('');

    const visible = rows.filter((row) =>
        row.name.toLowerCase().includes(search.toLowerCase()),
    );

    return (
        <AdminLayout
            title="Daftar Penjual"
            heading="Penjual"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Penjual' }]}
            actions={
                <Button href="/admin/penjual/tambah" icon="solar:add-circle-broken" size="sm">
                    Tambah Penjual
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar search={search} onSearch={setSearch} placeholder="Cari penjual..." />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.name}
                    empty="Penjual tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'name') {
                            return (
                                <Link
                                    href="/admin/penjual/detail"
                                    className="flex items-center gap-2.5"
                                >
                                    <img
                                        src={row.logo}
                                        alt=""
                                        className="h-9 w-9 shrink-0 rounded-lg bg-admin-hover object-contain p-1.5 dark:bg-admin-dark-hover"
                                    />
                                    <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {row.name}
                                    </span>
                                </Link>
                            );
                        }

                        if (key === 'revenue') {
                            return (
                                <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {money(row.revenue)}
                                </span>
                            );
                        }

                        if (key === 'rating') {
                            return (
                                <span className="inline-flex items-center justify-end gap-1">
                                    <Icon name="solar:star-bold" size={14} className="text-warning" />
                                    {row.rating.toLocaleString('id-ID', {
                                        minimumFractionDigits: 1,
                                    })}
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
                                    viewHref="/admin/penjual/detail"
                                    editHref="/admin/penjual/ubah"
                                    onDelete={() =>
                                        setRows((current) =>
                                            current.filter((item) => item.name !== row.name),
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
