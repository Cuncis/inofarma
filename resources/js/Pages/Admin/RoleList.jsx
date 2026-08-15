import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import RowActions from '@/Components/Admin/RowActions';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';
import { roles as seed } from '@/Components/Admin/data';

const columns = [
    { key: 'name', label: 'Peran' },
    { key: 'description', label: 'Deskripsi' },
    { key: 'users', label: 'Pengguna', align: 'right' },
    { key: 'permissions', label: 'Hak Akses', align: 'right' },
    { key: 'actions', label: '', align: 'right' },
];

export default function RoleList() {
    const [rows, setRows] = useState(seed);
    const [search, setSearch] = useState('');

    const visible = rows.filter((row) => row.name.toLowerCase().includes(search.toLowerCase()));

    return (
        <AdminLayout
            title="Daftar Peran"
            heading="Peran"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Peran' }]}
            actions={
                <Button href="/admin/peran/tambah" icon="solar:add-circle-broken" size="sm">
                    Tambah Peran
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar search={search} onSearch={setSearch} placeholder="Cari peran..." />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.name}
                    empty="Peran tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'name') {
                            return (
                                <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                    {row.name}
                                </span>
                            );
                        }

                        if (key === 'permissions') {
                            return <Badge tone="brand">{row.permissions} izin</Badge>;
                        }

                        if (key === 'actions') {
                            return (
                                <RowActions
                                    label={row.name}
                                    editHref="/admin/peran/ubah"
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
