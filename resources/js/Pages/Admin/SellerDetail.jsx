import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import StatCard from '@/Components/Admin/StatCard';
import Table from '@/Components/Admin/Table';
import { money, statusTone } from '@/Components/Admin/data';

export default function SellerDetail({ seller, products }) {
    return (
        <AdminLayout
            title="Detail Penjual"
            heading={seller.name}
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Penjual', href: '/admin/penjual' },
                { label: 'Detail' },
            ]}
            actions={
                <Button href={`/admin/penjual/${seller.id}/ubah`} size="sm" icon="solar:pen-2-broken">
                    Ubah
                </Button>
            }
        >
            <Card className="mb-5">
                <div className="flex flex-wrap items-center gap-4">
                    <img
                        src={seller.logo}
                        alt=""
                        className="h-16 w-16 shrink-0 rounded-xl bg-admin-hover object-contain p-3 dark:bg-admin-dark-hover"
                    />

                    <div className="min-w-0 flex-1">
                        <div className="flex flex-wrap items-center gap-2">
                            <h2 className="text-lg font-semibold text-admin-heading dark:text-admin-dark-heading">
                                {seller.name}
                            </h2>
                            <Badge tone={statusTone(seller.status)}>{seller.status}</Badge>
                        </div>

                        <p className="mt-1 text-[13px] text-admin-muted dark:text-admin-dark-muted">
                            {seller.owner} · {seller.city}
                        </p>
                    </div>

                    <span className="flex items-center gap-1.5 rounded-lg bg-admin-hover px-3 py-2 dark:bg-admin-dark-hover">
                        <Icon name="solar:document-text-bold" size={16} className="text-brand" />
                        <span className="text-[13px] font-bold text-admin-heading dark:text-admin-dark-heading">
                            {seller.license}
                        </span>
                    </span>
                </div>
            </Card>

            <div className="mb-5 grid gap-4 sm:grid-cols-3">
                <StatCard
                    label="Total Produk"
                    value={String(seller.products)}
                    icon="solar:box-bold-duotone"
                />
                <StatCard
                    label="Pendapatan"
                    value={money(seller.revenue)}
                    icon="solar:wallet-money-bold-duotone"
                />
                <StatCard
                    label="Bergabung"
                    value={seller.joined}
                    icon="solar:calendar-bold-duotone"
                />
            </div>

            <Card title="Produk Penjual" bodyClassName="p-0">
                <Table
                    columns={[
                        { key: 'name', label: 'Produk' },
                        { key: 'category', label: 'Kategori' },
                        { key: 'price', label: 'Harga', align: 'right' },
                        { key: 'stock', label: 'Stok', align: 'right' },
                        { key: 'status', label: 'Status' },
                    ]}
                    rows={products}
                    rowKey={(row) => row.id}
                    empty="Penjual ini belum menjual produk apa pun."

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
        </AdminLayout>
    );
}
