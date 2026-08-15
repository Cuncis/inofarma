import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import Table from '@/Components/Admin/Table';
import { money, statusTone } from '@/Components/Admin/data';

export default function OrderDetail({ order, statuses }) {
    // The lifecycle runs in order; Dibatalkan sits outside it.
    const lifecycle = statuses.filter((status) => status !== 'Dibatalkan');
    const reached = lifecycle.indexOf(order.status);
    const cancelled = order.status === 'Dibatalkan';

    return (
        <AdminLayout
            title={`Pesanan #${order.id}`}
            heading={`Pesanan #${order.id}`}
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pesanan', href: '/admin/pesanan' },
                { label: `#${order.id}` },
            ]}
            actions={
                <Button
                    href={`/admin/pesanan/${order.id}/ubah`}
                    size="sm"
                    icon="solar:pen-2-broken"
                >
                    Ubah
                </Button>
            }
        >
            <div className="grid gap-5 lg:grid-cols-3">
                <div className="space-y-5 lg:col-span-2">
                    <Card title="Item Pesanan" bodyClassName="p-0">
                        <Table
                            columns={[
                                { key: 'name', label: 'Produk' },
                                { key: 'qty', label: 'Jumlah', align: 'right' },
                                { key: 'price', label: 'Harga', align: 'right' },
                                { key: 'sum', label: 'Subtotal', align: 'right' },
                            ]}
                            rows={order.items}
                            rowKey={(row) => row.productId}
                            renderCell={(row, key) => {
                                if (key === 'name') {
                                    return (
                                        <Link
                                            href={`/admin/produk/${row.productId}`}
                                            className="font-medium text-admin-heading hover:text-brand dark:text-admin-dark-heading"
                                        >
                                            {row.name}
                                        </Link>
                                    );
                                }

                                if (key === 'price') {
                                    return money(row.price);
                                }

                                if (key === 'sum') {
                                    return (
                                        <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                            {money(row.qty * row.price)}
                                        </span>
                                    );
                                }

                                return row[key];
                            }}
                        />

                        <div className="ml-auto w-full max-w-xs space-y-2 px-5 py-4 text-[13px]">
                            <div className="flex justify-between">
                                <span className="text-admin-muted dark:text-admin-dark-muted">Subtotal</span>
                                <span>{money(order.subtotal)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-admin-muted dark:text-admin-dark-muted">Ongkos kirim</span>
                                <span>{money(order.shipping)}</span>
                            </div>
                            <div className="flex justify-between border-t border-admin-border pt-2 text-[15px] font-bold text-admin-heading dark:border-admin-dark-border dark:text-admin-dark-heading">
                                <span>Total</span>
                                <span>{money(order.total)}</span>
                            </div>
                        </div>
                    </Card>

                    <Card title="Status Pesanan">
                        {cancelled ? (
                            <p className="flex items-center gap-2.5 rounded-lg bg-[#fdecec] px-3.5 py-3 text-[13px] text-danger-deep dark:bg-danger/20 dark:text-danger">
                                <Icon name="solar:danger-triangle-broken" size={18} />
                                Pesanan ini dibatalkan.
                            </p>
                        ) : (
                            <ol className="relative space-y-5 border-l border-admin-border pl-6 dark:border-admin-dark-border">
                                {lifecycle.map((status, index) => {
                                    const done = index <= reached;

                                    return (
                                        <li key={status} className="relative">
                                            <span
                                                className={`absolute -left-[31px] flex h-5 w-5 items-center justify-center rounded-full ${
                                                    done
                                                        ? 'bg-brand text-white dark:bg-brand-lift'
                                                        : 'border-2 border-admin-border bg-admin-card dark:border-admin-dark-border dark:bg-admin-dark-card'
                                                }`}
                                            >
                                                {done ? (
                                                    <Icon name="solar:check-circle-broken" size={12} />
                                                ) : null}
                                            </span>

                                            <p
                                                className={`text-[13px] font-semibold ${
                                                    done
                                                        ? 'text-admin-heading dark:text-admin-dark-heading'
                                                        : 'text-admin-muted dark:text-admin-dark-muted'
                                                }`}
                                            >
                                                {status}
                                            </p>
                                        </li>
                                    );
                                })}
                            </ol>
                        )}
                    </Card>
                </div>

                <div className="space-y-5">
                    <Card title="Ringkasan">
                        <dl className="space-y-3 text-[13px]">
                            <div className="flex justify-between">
                                <dt className="text-admin-muted dark:text-admin-dark-muted">Status</dt>
                                <dd>
                                    <Badge tone={statusTone(order.status)}>{order.status}</Badge>
                                </dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-admin-muted dark:text-admin-dark-muted">Tanggal</dt>
                                <dd className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                    {order.date}
                                </dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-admin-muted dark:text-admin-dark-muted">Pembayaran</dt>
                                <dd className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                    {order.payment}
                                </dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-admin-muted dark:text-admin-dark-muted">Jumlah item</dt>
                                <dd className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                    {order.itemCount}
                                </dd>
                            </div>
                        </dl>
                    </Card>

                    <Card title="Pelanggan">
                        <div className="flex items-center gap-3">
                            {order.customerAvatar ? (
                                <img
                                    src={order.customerAvatar}
                                    alt=""
                                    className="h-11 w-11 rounded-full object-cover"
                                />
                            ) : null}

                            <div className="min-w-0">
                                {order.customerId ? (
                                    <Link
                                        href={`/admin/pelanggan/${order.customerId}`}
                                        className="block truncate text-[13px] font-semibold text-admin-heading hover:text-brand dark:text-admin-dark-heading"
                                    >
                                        {order.customerName}
                                    </Link>
                                ) : (
                                    <p className="truncate text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                        {order.customerName}
                                    </p>
                                )}
                                <p className="truncate text-xs text-admin-muted dark:text-admin-dark-muted">
                                    {order.customerEmail}
                                </p>
                            </div>
                        </div>
                    </Card>

                    {order.note ? (
                        <Card title="Catatan">
                            <p className="text-[13px] leading-relaxed text-admin-body dark:text-admin-dark-body">
                                {order.note}
                            </p>
                        </Card>
                    ) : null}
                </div>
            </div>
        </AdminLayout>
    );
}
