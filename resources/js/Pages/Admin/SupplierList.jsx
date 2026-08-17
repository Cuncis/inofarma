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
    { key: 'name', label: 'Pemasok' },
    { key: 'owner', label: 'Pemilik' },
    { key: 'city', label: 'Kota' },
    { key: 'products', label: 'Produk', align: 'right' },
    { key: 'revenue', label: 'Pendapatan', align: 'right' },
    { key: 'joined', label: 'Bergabung' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

export default function SupplierList({ suppliers }) {
    const [search, setSearch] = useState('');
    const [pendingDelete, setPendingDelete] = useState(null);
    const [deleting, setDeleting] = useState(false);

    const visible = suppliers.filter(
        (row) =>
            row.name.toLowerCase().includes(search.toLowerCase()) ||
            row.owner.toLowerCase().includes(search.toLowerCase()),
    );

    const confirmDelete = () => {
        setDeleting(true);

        router.delete(`/admin/pemasok/${pendingDelete.id}`, {
            preserveScroll: true,
            onFinish: () => {
                setDeleting(false);
                setPendingDelete(null);
            },
        });
    };

    // Stocked products keep a supplier on file; explain before firing the request.
    const hasProducts = pendingDelete?.products > 0;

    return (
        <AdminLayout
            title="Daftar Pemasok"
            heading="Pemasok"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Pemasok' }]}
            actions={
                <>
                    <Button
                        variant="outline"
                        size="sm"
                        icon="solar:restart-broken"
                        onClick={() =>
                            router.post('/admin/pemasok/reset', {}, { preserveScroll: true })
                        }
                    >
                        Atur Ulang Data
                    </Button>

                    <Button href="/admin/pemasok/tambah" icon="solar:add-circle-broken" size="sm">
                        Tambah Pemasok
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
                    empty="Pemasok tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'name') {
                            return (
                                <Link
                                    href={`/admin/pemasok/${row.id}`}
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
                                    viewHref={`/admin/pemasok/${row.id}`}
                                    editHref={`/admin/pemasok/${row.id}/ubah`}
                                    onDelete={() => setPendingDelete(row)}
                                />
                            );
                        }

                        return row[key];
                    }}
                />

                <div className="border-t border-admin-border px-5 py-3 text-xs text-admin-muted dark:border-admin-dark-border dark:text-admin-dark-muted">
                    Menampilkan {visible.length} dari {suppliers.length} pemasok
                </div>
            </Card>

            <ConfirmDialog
                open={Boolean(pendingDelete)}
                title={hasProducts ? 'Pemasok masih memasok produk' : 'Hapus pemasok?'}
                body={
                    pendingDelete
                        ? hasProducts
                            ? `"${pendingDelete.name}" masih memasok ${pendingDelete.products} produk. Pindahkan produk tersebut ke pemasok lain sebelum menghapusnya.`
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
