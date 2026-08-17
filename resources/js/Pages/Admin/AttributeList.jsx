import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import ConfirmDialog from '@/Components/Admin/ConfirmDialog';
import RowActions from '@/Components/Admin/RowActions';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';

const columns = [
    { key: 'name', label: 'Atribut' },
    { key: 'slug', label: 'Slug' },
    { key: 'type', label: 'Tipe' },
    { key: 'values', label: 'Nilai' },
    { key: 'actions', label: '', align: 'right' },
];

export default function AttributeList({ attributes }) {
    const [search, setSearch] = useState('');
    const [pendingDelete, setPendingDelete] = useState(null);
    const [deleting, setDeleting] = useState(false);

    const visible = attributes.filter((row) => row.name.toLowerCase().includes(search.toLowerCase()));

    const confirmDelete = () => {
        setDeleting(true);

        router.delete(`/admin/atribut/${pendingDelete.id}`, {
            preserveScroll: true,
            onFinish: () => {
                setDeleting(false);
                setPendingDelete(null);
            },
        });
    };

    return (
        <AdminLayout
            title="Daftar Atribut"
            heading="Atribut"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Atribut' }]}
            actions={
                <Button href="/admin/atribut/tambah" icon="solar:add-circle-broken" size="sm">
                    Tambah Atribut
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar search={search} onSearch={setSearch} placeholder="Cari atribut..." />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.slug}
                    empty="Atribut tidak ditemukan."
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

                        if (key === 'values') {
                            return row.values.length ? (
                                <span className="flex flex-wrap gap-1">
                                    {row.values.map((value) => (
                                        <Badge key={value}>{value}</Badge>
                                    ))}
                                </span>
                            ) : (
                                <span className="text-admin-muted dark:text-admin-dark-muted">—</span>
                            );
                        }

                        if (key === 'actions') {
                            return (
                                <RowActions
                                    label={row.name}
                                    editHref={`/admin/atribut/${row.id}/ubah`}
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
                title="Hapus atribut?"
                body={pendingDelete ? `"${pendingDelete.name}" akan dihapus. Tindakan ini tidak bisa dibatalkan.` : ''}
                confirmLabel="Hapus"
                processing={deleting}
                onConfirm={confirmDelete}
                onCancel={() => setPendingDelete(null)}
            />
        </AdminLayout>
    );
}
