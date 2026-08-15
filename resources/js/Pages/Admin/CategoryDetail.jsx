import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Table from '@/Components/Admin/Table';
import { categories, money, products, statusTone } from '@/Components/Admin/data';

const category = categories[0];

export default function CategoryDetail() {
    return (
        <AdminLayout
            title="Detail Kategori"
            heading={category.name}
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Kategori', href: '/admin/kategori' },
                { label: 'Detail' },
            ]}
            actions={
                <Button href="/admin/kategori/ubah" size="sm" icon="solar:pen-2-broken">
                    Ubah
                </Button>
            }
        >
            <div className="grid gap-5 lg:grid-cols-3">
                <Card title="Informasi">
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
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Jumlah produk</dt>
                            <dd className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                {category.products}
                            </dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Status</dt>
                            <dd>
                                <Badge tone={statusTone(category.status)}>{category.status}</Badge>
                            </dd>
                        </div>
                    </dl>

                    <p className="mt-4 border-t border-admin-border pt-4 text-[13px] leading-relaxed text-admin-body dark:border-admin-dark-border dark:text-admin-dark-body">
                        Obat yang dapat dibeli tanpa resep dokter dan dijual bebas di apotek maupun
                        toko obat berizin.
                    </p>
                </Card>

                <Card title="Produk dalam Kategori" bodyClassName="p-0" className="lg:col-span-2">
                    <Table
                        columns={[
                            { key: 'name', label: 'Produk' },
                            { key: 'price', label: 'Harga', align: 'right' },
                            { key: 'stock', label: 'Stok', align: 'right' },
                            { key: 'status', label: 'Status' },
                        ]}
                        rows={products.filter((product) => product.category === category.name)}
                        rowKey={(row) => row.id}
                        empty="Belum ada produk pada kategori ini."
                        renderCell={(row, key) => {
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
