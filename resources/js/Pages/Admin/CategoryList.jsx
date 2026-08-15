import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import RowActions from '@/Components/Admin/RowActions';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';
import { categories as seed, statusTone } from '@/Components/Admin/data';

const columns = [
    { key: 'name', label: 'Kategori' },
    { key: 'slug', label: 'Slug' },
    { key: 'products', label: 'Jumlah Produk', align: 'right' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

export default function CategoryList() {
    const [rows, setRows] = useState(seed);
    const [search, setSearch] = useState('');

    const visible = rows.filter((row) =>
        row.name.toLowerCase().includes(search.toLowerCase()),
    );

    return (
        <AdminLayout
            title="Daftar Kategori"
            heading="Kategori"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Kategori' }]}
            actions={
                <Button href="/admin/kategori/tambah" icon="solar:add-circle-broken" size="sm">
                    Tambah Kategori
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar search={search} onSearch={setSearch} placeholder="Cari kategori..." />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.slug}
                    empty="Kategori tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'name') {
                            return (
                                <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                    {row.name}
                                </span>
                            );
                        }

                        if (key === 'slug') {
                            return (
                                <code className="rounded bg-admin-hover px-1.5 py-0.5 text-xs dark:bg-admin-dark-hover">
                                    {row.slug}
                                </code>
                            );
                        }

                        if (key === 'status') {
                            return <Badge tone={statusTone(row.status)}>{row.status}</Badge>;
                        }

                        if (key === 'actions') {
                            return (
                                <RowActions
                                    label={row.name}
                                    viewHref="/admin/kategori/detail"
                                    editHref="/admin/kategori/ubah"
                                    onDelete={() =>
                                        setRows((current) =>
                                            current.filter((item) => item.slug !== row.slug),
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
