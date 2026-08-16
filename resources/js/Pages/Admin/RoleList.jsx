import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import ConfirmDialog from '@/Components/Admin/ConfirmDialog';
import RowActions from '@/Components/Admin/RowActions';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';

const columns = [
    { key: 'name', label: 'Peran' },
    { key: 'description', label: 'Deskripsi' },
    { key: 'users', label: 'Pengguna', align: 'right' },
    { key: 'permissions', label: 'Hak Akses', align: 'right' },
    { key: 'actions', label: '', align: 'right' },
];

/**
 * @param {{ roles: object[] }} props
 */
export default function RoleList({ roles }) {
    const [search, setSearch] = useState('');
    const [pendingDelete, setPendingDelete] = useState(null);
    const [deleting, setDeleting] = useState(false);

    const visible = roles.filter((role) => role.name.toLowerCase().includes(search.toLowerCase()));

    const confirmDelete = () => {
        setDeleting(true);

        router.delete(`/admin/peran/${pendingDelete.name}`, {
            preserveScroll: true,
            onFinish: () => {
                setDeleting(false);
                setPendingDelete(null);
            },
        });
    };

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
                                <Link
                                    href={`/admin/peran/${row.name}/ubah`}
                                    className="font-medium text-admin-heading dark:text-admin-dark-heading"
                                >
                                    {row.name}
                                </Link>
                            );
                        }

                        if (key === 'permissions') {
                            return <Badge tone="brand">{row.permissions} izin</Badge>;
                        }

                        if (key === 'actions') {
                            return (
                                <RowActions
                                    label={row.name}
                                    editHref={`/admin/peran/${row.name}/ubah`}
                                    onDelete={() => setPendingDelete(row)}
                                />
                            );
                        }

                        return row[key];
                    }}
                />
            </Card>

            <ConfirmDialog
                open={Boolean(pendingDelete)}
                title="Hapus peran?"
                body={
                    pendingDelete
                        ? `"${pendingDelete.name}" akan dihapus. Peran yang masih dipakai staf tidak bisa dihapus.`
                        : ''
                }
                confirmLabel="Hapus"
                processing={deleting}
                onConfirm={confirmDelete}
                onCancel={() => setPendingDelete(null)}
            />
        </AdminLayout>
    );
}
