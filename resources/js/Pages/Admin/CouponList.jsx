import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import RowActions from '@/Components/Admin/RowActions';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';
import { coupons as seed, money, statusTone } from '@/Components/Admin/data';

const columns = [
    { key: 'code', label: 'Kode' },
    { key: 'type', label: 'Tipe' },
    { key: 'value', label: 'Nilai' },
    { key: 'minimum', label: 'Min. Belanja', align: 'right' },
    { key: 'used', label: 'Terpakai', align: 'right' },
    { key: 'expires', label: 'Berlaku Sampai' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

export default function CouponList() {
    const [rows, setRows] = useState(seed);
    const [search, setSearch] = useState('');

    const visible = rows.filter((row) => row.code.toLowerCase().includes(search.toLowerCase()));

    return (
        <AdminLayout
            title="Daftar Kupon"
            heading="Kupon"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Kupon' }]}
            actions={
                <Button href="/admin/kupon/tambah" icon="solar:add-circle-broken" size="sm">
                    Tambah Kupon
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar search={search} onSearch={setSearch} placeholder="Cari kode kupon..." />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.code}
                    empty="Kupon tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'code') {
                            return (
                                <code className="rounded bg-blush px-2 py-1 text-xs font-bold tracking-wider text-brand dark:bg-brand/20 dark:text-white">
                                    {row.code}
                                </code>
                            );
                        }

                        if (key === 'minimum') {
                            return money(row.minimum);
                        }

                        if (key === 'used') {
                            return (
                                <span>
                                    {row.used}
                                    <span className="text-admin-muted dark:text-admin-dark-muted">
                                        {' '}/ {row.quota}
                                    </span>
                                </span>
                            );
                        }

                        if (key === 'status') {
                            return <Badge tone={statusTone(row.status)}>{row.status}</Badge>;
                        }

                        if (key === 'actions') {
                            return (
                                <RowActions
                                    label={row.code}
                                    editHref="/admin/kupon/tambah"
                                    onDelete={() =>
                                        setRows((current) =>
                                            current.filter((item) => item.code !== row.code),
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
