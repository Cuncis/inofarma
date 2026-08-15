import { useState } from 'react';
import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Icon from '@/Components/Admin/Icon';
import { money, products, statusTone } from '@/Components/Admin/data';

export default function ProductGrid() {
    const [search, setSearch] = useState('');

    const visible = products.filter((product) =>
        product.name.toLowerCase().includes(search.toLowerCase()),
    );

    return (
        <AdminLayout
            title="Grid Produk"
            heading="Produk"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Produk', href: '/admin/produk' },
                { label: 'Grid' },
            ]}
            actions={
                <Button href="/admin/produk/tambah" icon="solar:add-circle-broken" size="sm">
                    Tambah Produk
                </Button>
            }
        >
            <div className="mb-5 flex flex-wrap items-center gap-3">
                <div className="relative min-w-[220px] flex-1">
                    <Icon
                        name="solar:magnifer-linear"
                        size={17}
                        className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-admin-muted"
                    />
                    <input
                        type="search"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Cari produk..."
                        className="h-10 w-full rounded-lg border border-admin-border bg-admin-card pl-10 pr-3 text-[13px] text-admin-body placeholder:text-admin-muted focus:border-brand focus:outline-none focus:ring-0 dark:border-admin-dark-border dark:bg-admin-dark-card dark:text-admin-dark-body"
                    />
                </div>

                <Button href="/admin/produk" variant="outline" size="sm" icon="solar:filter-broken">
                    Tampilan Daftar
                </Button>
            </div>

            {visible.length === 0 ? (
                <p className="rounded-xl border border-admin-border bg-admin-card p-10 text-center text-[13px] text-admin-muted dark:border-admin-dark-border dark:bg-admin-dark-card dark:text-admin-dark-muted">
                    Produk tidak ditemukan.
                </p>
            ) : (
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {visible.map((product) => (
                        <div
                            key={product.id}
                            className="overflow-hidden rounded-xl border border-admin-border bg-admin-card shadow-card dark:border-admin-dark-border dark:bg-admin-dark-card"
                        >
                            <Link
                                href="/admin/produk/detail"
                                className="flex h-40 items-center justify-center bg-admin-hover p-5 dark:bg-admin-dark-hover"
                            >
                                <img
                                    src={product.image}
                                    alt={product.name}
                                    className="h-full max-w-full object-contain"
                                />
                            </Link>

                            <div className="p-4">
                                <div className="mb-2 flex items-start justify-between gap-2">
                                    <Link
                                        href="/admin/produk/detail"
                                        className="text-[13px] font-semibold text-admin-heading hover:text-brand dark:text-admin-dark-heading"
                                    >
                                        {product.name}
                                    </Link>
                                    <Badge tone={statusTone(product.status)}>{product.status}</Badge>
                                </div>

                                <p className="mb-3 text-xs text-admin-muted dark:text-admin-dark-muted">
                                    {product.category}
                                </p>

                                <div className="flex items-center justify-between">
                                    <span className="text-[15px] font-bold text-admin-heading dark:text-admin-dark-heading">
                                        {money(product.price)}
                                    </span>
                                    <span className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                        Stok {product.stock}
                                    </span>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </AdminLayout>
    );
}
