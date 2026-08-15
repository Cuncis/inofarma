import { useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import RowActions from '@/Components/Admin/RowActions';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';
import { money, products as seed, statusTone } from '@/Components/Admin/data';

const columns = [
    { key: 'name', label: 'Produk' },
    { key: 'category', label: 'Kategori' },
    { key: 'price', label: 'Harga', align: 'right' },
    { key: 'stock', label: 'Stok', align: 'right' },
    { key: 'sold', label: 'Terjual', align: 'right' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

export default function ProductList() {
    const [rows, setRows] = useState(seed);
    const [search, setSearch] = useState('');
    const [category, setCategory] = useState('Semua Kategori');

    const categories = useMemo(
        () => ['Semua Kategori', ...new Set(seed.map((product) => product.category))],
        [],
    );

    const visible = rows.filter((product) => {
        const matchesSearch = product.name.toLowerCase().includes(search.toLowerCase());
        const matchesCategory =
            category === 'Semua Kategori' || product.category === category;

        return matchesSearch && matchesCategory;
    });

    return (
        <AdminLayout
            title="Daftar Produk"
            heading="Produk"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Produk' },
            ]}
            actions={
                <Button href="/admin/produk/tambah" icon="solar:add-circle-broken" size="sm">
                    Tambah Produk
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar
                    search={search}
                    onSearch={setSearch}
                    placeholder="Cari produk..."
                    filter={{ value: category, onChange: setCategory, options: categories }}
                />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.id}
                    empty="Produk tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'name') {
                            return (
                                <Link
                                    href="/admin/produk/detail"
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
                                <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {money(row.price)}
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
                                    viewHref="/admin/produk/detail"
                                    editHref="/admin/produk/ubah"
                                    onDelete={() =>
                                        setRows((current) =>
                                            current.filter((item) => item.id !== row.id),
                                        )
                                    }
                                />
                            );
                        }

                        return row[key];
                    }}
                />

                <div className="flex items-center justify-between border-t border-admin-border px-5 py-3 text-xs text-admin-muted dark:border-admin-dark-border dark:text-admin-dark-muted">
                    <span>
                        Menampilkan {visible.length} dari {rows.length} produk
                    </span>

                    <div className="flex gap-1">
                        <Button variant="outline" size="sm" disabled>
                            Sebelumnya
                        </Button>
                        <Button variant="outline" size="sm">
                            Berikutnya
                        </Button>
                    </div>
                </div>
            </Card>
        </AdminLayout>
    );
}
