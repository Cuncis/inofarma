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
import { money, statusTone } from '@/Components/Admin/data';

const columns = [
    { key: 'name', label: 'Penjual' },
    { key: 'owner', label: 'Pemilik' },
    { key: 'city', label: 'Kota' },
    { key: 'products', label: 'Produk', align: 'right' },
    { key: 'revenue', label: 'Pendapatan', align: 'right' },
    { key: 'joined', label: 'Bergabung' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

export default function SellerList({ sellers }) {
    const [search, setSearch] = useState('');
    const [pendingDelete, setPendingDelete] = useState(null);
    const [deleting, setDeleting] = useState(false);

    const visible = sellers.filter(
        (row) =>
            row.name.toLowerCase().includes(search.toLowerCase()) ||
            row.owner.toLowerCase().includes(search.toLowerCase()),
    );

    const confirmDelete = () => {
        setDeleting(true);

        router.delete(`/admin/penjual/${pendingDelete.id}`, {
            preserveScroll: true,
            onFinish: () => {
                setDeleting(false);
                setPendingDelete(null);
            },
        });
    };

    // Stocked products keep a seller on file; explain before firing the request.
    const hasProducts = pendingDelete?.products > 0;

    return (
        <AdminLayout
            title="Daftar Penjual"
            heading="Penjual"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Penjual' }]}
            actions={
                <>
                    <Button
                        variant="outline"
                        size="sm"
                        icon="solar:restart-broken"
                        onClick={() =>
                            router.post('/admin/penjual/reset', {}, { preserveScroll: true })
                        }
                    >
                        Atur Ulang Data
                    </Button>

                    <Button href="/admin/penjual/tambah" icon="solar:add-circle-broken" size="sm">
                        Tambah Penjual
                    </Button>
                </>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar
                    search={search}
                    onSearch={setSearch}
                    placeholder="Cari nama toko atau pemilik..."
                />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.name}
                    empty="Penjual tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'name') {
                            return (
                                <Link
                                    href={`/admin/penjual/${row.id}`}
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

                        if (key === 'status') {
                            return <Badge tone={statusTone(row.status)}>{row.status}</Badge>;
                        }

                        if (key === 'actions') {
                            return (
                                <RowActions
                                    label={row.name}
                                    viewHref={`/admin/penjual/${row.id}`}
                                    editHref={`/admin/penjual/${row.id}/ubah`}
                                    onDelete={() => setPendingDelete(row)}
                                />
                            );
                        }

                        return row[key];
                    }}
                />

                <div className="border-t border-admin-border px-5 py-3 text-xs text-admin-muted dark:border-admin-dark-border dark:text-admin-dark-muted">
                    Menampilkan {visible.length} dari {sellers.length} penjual
                </div>
            </Card>

            <ConfirmDialog
                open={Boolean(pendingDelete)}
                title={hasProducts ? 'Penjual masih menjual produk' : 'Hapus penjual?'}
                body={
                    pendingDelete
                        ? hasProducts
                            ? `"${pendingDelete.name}" masih menjual ${pendingDelete.products} produk. Pindahkan produk tersebut ke penjual lain sebelum menghapusnya.`
                            : `"${pendingDelete.name}" akan dihapus. Tindakan ini tidak bisa dibatalkan.`
                        : ''
                }
                confirmLabel={hasProducts ? 'Mengerti' : 'Hapus'}
                processing={deleting}
                onConfirm={hasProducts ? () => setPendingDelete(null) : confirmDelete}
                onCancel={() => setPendingDelete(null)}
            />
        </AdminLayout>
    );
}
