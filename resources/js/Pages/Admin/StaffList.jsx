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
    { key: 'name', label: 'Nama' },
    { key: 'branchName', label: 'Cabang' },
    { key: 'roles', label: 'Peran' },
    { key: 'isActive', label: 'Status' },
    { key: 'twoFactorEnabled', label: '2FA' },
    { key: 'actions', label: '', align: 'right' },
];

/**
 * @param {{ staff: object[] }} props
 */
export default function StaffList({ staff }) {
    const [search, setSearch] = useState('');
    const [pendingDelete, setPendingDelete] = useState(null);
    const [deleting, setDeleting] = useState(false);

    const visible = staff.filter(
        (row) =>
            row.name.toLowerCase().includes(search.toLowerCase()) ||
            row.email.toLowerCase().includes(search.toLowerCase()),
    );

    const confirmDelete = () => {
        setDeleting(true);

        router.delete(`/admin/staf/${pendingDelete.id}`, {
            preserveScroll: true,
            onFinish: () => {
                setDeleting(false);
                setPendingDelete(null);
            },
        });
    };

    return (
        <AdminLayout
            title="Staf Admin"
            heading="Staf Admin"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Staf Admin' }]}
            actions={
                <Button href="/admin/staf/tambah" icon="solar:add-circle-broken" size="sm">
                    Tambah Staf
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar search={search} onSearch={setSearch} placeholder="Cari nama atau email..." />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.id}
                    empty="Staf tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'name') {
                            return (
                                <Link href={`/admin/staf/${row.id}/ubah`} className="flex flex-col">
                                    <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {row.name}
                                    </span>
                                    <span className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                        {row.email}
                                    </span>
                                </Link>
                            );
                        }

                        if (key === 'roles') {
                            return row.roles.length ? (
                                <div className="flex flex-wrap gap-1">
                                    {row.roles.map((role) => (
                                        <Badge key={role} tone="brand">
                                            {role}
                                        </Badge>
                                    ))}
                                </div>
                            ) : (
                                <span className="text-xs text-admin-muted dark:text-admin-dark-muted">—</span>
                            );
                        }

                        if (key === 'isActive') {
                            return (
                                <Badge tone={row.isActive ? 'success' : 'neutral'}>
                                    {row.isActive ? 'Aktif' : 'Nonaktif'}
                                </Badge>
                            );
                        }

                        if (key === 'twoFactorEnabled') {
                            return (
                                <Badge tone={row.twoFactorEnabled ? 'success' : 'neutral'}>
                                    {row.twoFactorEnabled ? 'Aktif' : 'Nonaktif'}
                                </Badge>
                            );
                        }

                        if (key === 'actions') {
                            return (
                                <RowActions
                                    label={row.name}
                                    editHref={`/admin/staf/${row.id}/ubah`}
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
                title="Hapus akun staf?"
                body={pendingDelete ? `"${pendingDelete.name}" akan dihapus dan tidak bisa masuk lagi.` : ''}
                confirmLabel="Hapus"
                processing={deleting}
                onConfirm={confirmDelete}
                onCancel={() => setPendingDelete(null)}
            />
        </AdminLayout>
    );
}
