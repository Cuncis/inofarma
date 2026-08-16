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
import { statusTone } from '@/Components/Admin/data';

const columns = [
    { key: 'name', label: 'Cabang' },
    { key: 'kota', label: 'Kota' },
    { key: 'stockCount', label: 'Produk Distok', align: 'right' },
    { key: 'openNow', label: 'Buka Sekarang' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

export default function BranchList({ branches }) {
    const [search, setSearch] = useState('');
    const [pendingDelete, setPendingDelete] = useState(null);
    const [deleting, setDeleting] = useState(false);

    const visible = branches.filter(
        (branch) =>
            branch.name.toLowerCase().includes(search.toLowerCase()) ||
            branch.kota.toLowerCase().includes(search.toLowerCase()),
    );

    const confirmDelete = () => {
        setDeleting(true);

        router.delete(`/admin/cabang/${pendingDelete.id}`, {
            preserveScroll: true,
            onFinish: () => {
                setDeleting(false);
                setPendingDelete(null);
            },
        });
    };

    const inUse = pendingDelete?.stockCount > 0;

    return (
        <AdminLayout
            title="Daftar Cabang"
            heading="Cabang"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Cabang' }]}
            actions={
                <Button href="/admin/cabang/tambah" icon="solar:add-circle-broken" size="sm">
                    Tambah Cabang
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar
                    search={search}
                    onSearch={setSearch}
                    placeholder="Cari nama cabang atau kota..."
                />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.id}
                    empty="Cabang tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'name') {
                            return (
                                <Link href={`/admin/cabang/${row.id}`} className="flex flex-col">
                                    <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {row.name}
                                    </span>
                                    <span className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                        {row.id}
                                    </span>
                                </Link>
                            );
                        }

                        if (key === 'stockCount') {
                            return (
                                <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {row.stockCount}
                                </span>
                            );
                        }

                        if (key === 'openNow') {
                            return (
                                <Badge tone={row.isOpenNow ? 'success' : 'neutral'}>
                                    {row.isOpenNow ? 'Buka' : 'Tutup'}
                                </Badge>
                            );
                        }

                        if (key === 'status') {
                            return <Badge tone={statusTone(row.status)}>{row.status}</Badge>;
                        }

                        if (key === 'actions') {
                            return (
                                <RowActions
                                    label={row.name}
                                    viewHref={`/admin/cabang/${row.id}`}
                                    editHref={`/admin/cabang/${row.id}/ubah`}
                                    onDelete={() => setPendingDelete(row)}
                                />
                            );
                        }

                        return row[key];
                    }}
                />

                <div className="border-t border-admin-border px-5 py-3 text-xs text-admin-muted dark:border-admin-dark-border dark:text-admin-dark-muted">
                    Menampilkan {visible.length} dari {branches.length} cabang
                </div>
            </Card>

            <ConfirmDialog
                open={Boolean(pendingDelete)}
                title={inUse ? 'Cabang masih punya stok' : 'Hapus cabang?'}
                body={
                    pendingDelete
                        ? inUse
                            ? `"${pendingDelete.name}" masih menyimpan stok produk. Ubah statusnya menjadi Tutup Permanen, jangan dihapus.`
                            : `"${pendingDelete.name}" akan dihapus. Tindakan ini tidak bisa dibatalkan.`
                        : ''
                }
                confirmLabel={inUse ? 'Mengerti' : 'Hapus'}
                processing={deleting}
                onConfirm={inUse ? () => setPendingDelete(null) : confirmDelete}
                onCancel={() => setPendingDelete(null)}
            />
        </AdminLayout>
    );
}
