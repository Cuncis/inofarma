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
    { key: 'name', label: 'Kategori' },
    { key: 'slug', label: 'Slug' },
    { key: 'products', label: 'Jumlah Produk', align: 'right' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

export default function CategoryList({ categories }) {
    const [search, setSearch] = useState('');
    const [pendingDelete, setPendingDelete] = useState(null);
    const [deleting, setDeleting] = useState(false);

    const visible = categories.filter(
        (category) =>
            category.name.toLowerCase().includes(search.toLowerCase()) ||
            category.slug.toLowerCase().includes(search.toLowerCase()),
    );

    const confirmDelete = () => {
        setDeleting(true);

        router.delete(`/admin/kategori/${pendingDelete.id}`, {
            preserveScroll: true,
            onFinish: () => {
                setDeleting(false);
                setPendingDelete(null);
            },
        });
    };

    // A category in use cannot be removed; say so up front instead of letting
    // the request come back with an error.
    const inUse = pendingDelete?.products > 0;

    return (
        <AdminLayout
            title="Daftar Kategori"
            heading="Kategori"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Kategori' }]}
            actions={
                <>
                    <Button
                        variant="outline"
                        size="sm"
                        icon="solar:restart-broken"
                        onClick={() =>
                            router.post('/admin/kategori/reset', {}, { preserveScroll: true })
                        }
                    >
                        Atur Ulang Data
                    </Button>

                    <Button href="/admin/kategori/tambah" icon="solar:add-circle-broken" size="sm">
                        Tambah Kategori
                    </Button>
                </>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar
                    search={search}
                    onSearch={setSearch}
                    placeholder="Cari nama kategori atau slug..."
                />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.id}
                    empty="Kategori tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'name') {
                            return (
                                <Link
                                    href={`/admin/kategori/${row.id}`}
                                    className="flex items-center gap-3"
                                >
                                    <img
                                        src={row.image}
                                        alt=""
                                        className="h-10 w-10 shrink-0 rounded-lg object-cover"
                                    />
                                    <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {row.name}
                                    </span>
                                </Link>
                            );
                        }

                        if (key === 'slug') {
                            return (
                                <code className="rounded bg-admin-hover px-1.5 py-0.5 text-xs dark:bg-admin-dark-hover">
                                    {row.slug}
                                </code>
                            );
                        }

                        if (key === 'products') {
                            return (
                                <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {row.products}
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
                                    viewHref={`/admin/kategori/${row.id}`}
                                    editHref={`/admin/kategori/${row.id}/ubah`}
                                    onDelete={() => setPendingDelete(row)}
                                />
                            );
                        }

                        return row[key];
                    }}
                />

                <div className="border-t border-admin-border px-5 py-3 text-xs text-admin-muted dark:border-admin-dark-border dark:text-admin-dark-muted">
                    Menampilkan {visible.length} dari {categories.length} kategori
                </div>
            </Card>

            <ConfirmDialog
                open={Boolean(pendingDelete)}
                title={inUse ? 'Kategori masih dipakai' : 'Hapus kategori?'}
                body={
                    pendingDelete
                        ? inUse
                            ? `"${pendingDelete.name}" masih dipakai ${pendingDelete.products} produk. Pindahkan produk tersebut ke kategori lain sebelum menghapusnya.`
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
