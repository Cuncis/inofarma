import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Table from '@/Components/Admin/Table';
import { money, statusTone } from '@/Components/Admin/data';

export default function CategoryDetail({ category, products }) {
    return (
        <AdminLayout
            title={`Kategori ${category.name}`}
            heading={category.name}
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Kategori', href: '/admin/kategori' },
                { label: category.name },
            ]}
            actions={
                <Button
                    href={`/admin/kategori/${category.id}/ubah`}
                    size="sm"
                    icon="solar:pen-2-broken"
                >
                    Ubah
                </Button>
            }
        >
            <div className="grid gap-5 lg:grid-cols-3">
                <Card title="Informasi">
                    <img
                        src={category.image}
                        alt=""
                        className="mb-4 h-32 w-full rounded-lg object-cover"
                    />

                    <dl className="space-y-3 text-[13px]">
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Nama</dt>
                            <dd className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                {category.name}
                            </dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Slug</dt>
                            <dd>
                                <code className="rounded bg-admin-hover px-1.5 py-0.5 text-xs dark:bg-admin-dark-hover">
                                    {category.slug}
                                </code>
                            </dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">
                                Jumlah produk
                            </dt>
                            <dd className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                {products.length}
                            </dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Status</dt>
                            <dd>
                                <Badge tone={statusTone(category.status)}>{category.status}</Badge>
                            </dd>
                        </div>
                    </dl>

                    {category.description ? (
                        <p className="mt-4 border-t border-admin-border pt-4 text-[13px] leading-relaxed text-admin-body dark:border-admin-dark-border dark:text-admin-dark-body">
                            {category.description}
                        </p>
                    ) : null}
                </Card>

                <Card title="Produk dalam Kategori" bodyClassName="p-0" className="lg:col-span-2">
                    <Table
                        columns={[
                            { key: 'name', label: 'Produk' },
                            { key: 'price', label: 'Harga', align: 'right' },
                            { key: 'stock', label: 'Stok', align: 'right' },
                            { key: 'status', label: 'Status' },
                        ]}
                        rows={products}
                        rowKey={(row) => row.id}
                        empty="Belum ada produk pada kategori ini."
                        renderCell={(row, key) => {
                            if (key === 'name') {
                                return (
                                    <Link
                                        href={`/admin/produk/${row.id}`}
                                        className="flex items-center gap-2.5"
                                    >
                                        <img
                                            src={row.image}
                                            alt=""
                                            className="h-9 w-9 shrink-0 rounded-lg bg-admin-hover object-contain p-1 dark:bg-admin-dark-hover"
                                        />
                                        <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                            {row.name}
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

                            return row[key];
                        }}
                    />
                </Card>
            </div>
        </AdminLayout>
    );
}
