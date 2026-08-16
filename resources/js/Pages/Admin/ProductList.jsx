import { useMemo, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import ConfirmDialog from '@/Components/Admin/ConfirmDialog';
import ProductGridView from '@/Components/Admin/ProductGridView';
import RowActions from '@/Components/Admin/RowActions';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';
import ViewToggle from '@/Components/Admin/ViewToggle';
import { money, statusTone } from '@/Components/Admin/data';

const VIEW_KEY = 'inofarma-admin-product-view';

const columns = [
    { key: 'name', label: 'Produk' },
    { key: 'category', label: 'Kategori' },
    { key: 'price', label: 'Harga', align: 'right' },
    { key: 'stock', label: 'Stok', align: 'right' },
    { key: 'sold', label: 'Terjual', align: 'right' },
    { key: 'stockStatus', label: 'Ketersediaan' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

export default function ProductList({ products, categories }) {
    const [search, setSearch] = useState('');
    const [category, setCategory] = useState('Semua Kategori');
    const [pendingDelete, setPendingDelete] = useState(null);
    const [deleting, setDeleting] = useState(false);

    // The view is a display preference, so it is remembered locally rather than
    // routed — switching never costs a round trip or loses the active filters.
    const [view, setView] = useState(
        () => (typeof window === 'undefined' ? 'daftar' : window.localStorage.getItem(VIEW_KEY) ?? 'daftar'),
    );

    const changeView = (next) => {
        setView(next);
        window.localStorage.setItem(VIEW_KEY, next);
    };

    const options = useMemo(() => ['Semua Kategori', ...categories], [categories]);

    const visible = products.filter((product) => {
        const matchesSearch =
            product.name.toLowerCase().includes(search.toLowerCase()) ||
            product.id.toLowerCase().includes(search.toLowerCase());

        return matchesSearch && (category === 'Semua Kategori' || product.category === category);
    });

    const confirmDelete = () => {
        setDeleting(true);

        router.delete(`/admin/produk/${pendingDelete.id}`, {
            preserveScroll: true,
            onFinish: () => {
                setDeleting(false);
                setPendingDelete(null);
            },
        });
    };

    return (
        <AdminLayout
            title="Daftar Produk"
            heading="Produk"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Produk' }]}
            actions={
                <>
                    <Button
                        variant="outline"
                        size="sm"
                        icon="solar:restart-broken"
                        onClick={() => router.post('/admin/produk/reset', {}, { preserveScroll: true })}
                    >
                        Atur Ulang Data
                    </Button>

                    <Button href="/admin/produk/tambah" icon="solar:add-circle-broken" size="sm">
                        Tambah Produk
                    </Button>
                </>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar
                    search={search}
                    onSearch={setSearch}
                    placeholder="Cari nama produk atau SKU..."
                    filter={{ value: category, onChange: setCategory, options }}
                >
                    <ViewToggle value={view} onChange={changeView} />
                </TableToolbar>

                {view === 'grid' ? (
                    <ProductGridView products={visible} onDelete={setPendingDelete} />
                ) : (
                    <Table
                        columns={columns}
                        rows={visible}
                        rowKey={(row) => row.id}
                        empty="Produk tidak ditemukan."
                        renderCell={(row, key) => {
                            if (key === 'name') {
                                return (
                                    <Link
                                        href={`/admin/produk/${row.id}`}
                                        className="flex items-center gap-3"
                                    >
                                        <img
                                            src={row.image}
                                            alt=""
                                            className="h-10 w-10 shrink-0 rounded-lg bg-admin-hover object-contain p-1 dark:bg-admin-dark-hover"
                                        />
                                        <span>
                                            <span className="block font-medium text-admin-heading dark:text-admin-dark-heading">
                                                {row.name}
                                            </span>
                                            <span className="block text-xs text-admin-muted dark:text-admin-dark-muted">
                                                {row.id}
                                            </span>
                                        </span>
                                    </Link>
                                );
                            }

                            if (key === 'price') {
                                return (
                                    <span>
                                        {row.oldPrice ? (
                                            <span className="mr-1.5 text-xs text-admin-muted line-through dark:text-admin-dark-muted">
                                                {money(row.oldPrice)}
                                            </span>
                                        ) : null}
                                        <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                            {money(row.price)}
                                        </span>
                                    </span>
                                );
                            }

                            if (key === 'status') {
                                return <Badge tone={statusTone(row.status)}>{row.status}</Badge>;
                            }

                            // Ketersediaan diturunkan dari stok seluruh cabang,
                            // terpisah dari status produk: produk boleh Aktif dan
                            // tetap habis di mana-mana.
                            if (key === 'stockStatus') {
                                return (
                                    <Badge tone={statusTone(row.stockStatus)}>{row.stockStatus}</Badge>
                                );
                            }

                            if (key === 'actions') {
                                return (
                                    <RowActions
                                        label={row.name}
                                        viewHref={`/admin/produk/${row.id}`}
                                        editHref={`/admin/produk/${row.id}/ubah`}
                                        onDelete={() => setPendingDelete(row)}
                                    />
                                );
                            }

                            return row[key];
                        }}
                    />
                )}

                <div className="flex items-center justify-between border-t border-admin-border px-5 py-3 text-xs text-admin-muted dark:border-admin-dark-border dark:text-admin-dark-muted">
                    <span>
                        Menampilkan {visible.length} dari {products.length} produk
                    </span>
                </div>
            </Card>

            <ConfirmDialog
                open={Boolean(pendingDelete)}
                title="Hapus produk?"
                body={
                    pendingDelete
                        ? `"${pendingDelete.name}" akan dihapus dari katalog. Tindakan ini tidak bisa dibatalkan.`
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
