import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import Table from '@/Components/Admin/Table';
import { money, statusTone } from '@/Components/Admin/data';

export default function CustomerDetail({ customer, orders }) {
    const details = [
        { label: 'Email', value: customer.email, icon: 'solar:letter-broken' },
        { label: 'Telepon', value: customer.phone, icon: 'solar:phone-broken' },
        { label: 'Kota', value: customer.city, icon: 'solar:city-broken' },
        { label: 'Bergabung', value: customer.joined, icon: 'solar:clock-circle-broken' },
    ];

    return (
        <AdminLayout
            title={`Pelanggan ${customer.name}`}
            heading={customer.name}
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pelanggan', href: '/admin/pelanggan' },
                { label: customer.name },
            ]}
            actions={
                <Button
                    href={`/admin/pelanggan/${customer.id}/ubah`}
                    size="sm"
                    icon="solar:pen-2-broken"
                >
                    Ubah
                </Button>
            }
        >
            <div className="grid gap-5 lg:grid-cols-3">
                <Card>
                    <div className="text-center">
                        <img
                            src={customer.avatar}
                            alt=""
                            className="mx-auto h-20 w-20 rounded-full object-cover"
                        />
                        <h2 className="mt-3 text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                            {customer.name}
                        </h2>
                        <p className="text-xs text-admin-muted dark:text-admin-dark-muted">
                            {customer.id}
                        </p>
                        <Badge tone={statusTone(customer.status)} className="mt-2">
                            {customer.status}
                        </Badge>
                    </div>

                    <dl className="mt-5 space-y-4 border-t border-admin-border pt-5 dark:border-admin-dark-border">
                        {details.map((detail) => (
                            <div key={detail.label} className="flex items-start gap-3">
                                <Icon
                                    name={detail.icon}
                                    size={17}
                                    className="mt-0.5 shrink-0 text-admin-muted"
                                />
                                <div className="min-w-0">
                                    <dt className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                        {detail.label}
                                    </dt>
                                    <dd className="truncate text-[13px] font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {detail.value}
                                    </dd>
                                </div>
                            </div>
                        ))}
                    </dl>

                    {customer.address ? (
                        <div className="mt-4 border-t border-admin-border pt-4 dark:border-admin-dark-border">
                            <p className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                Alamat
                            </p>
                            <p className="mt-1 text-[13px] leading-relaxed text-admin-body dark:text-admin-dark-body">
                                {customer.address}
                            </p>
                        </div>
                    ) : null}

                    <dl className="mt-4 grid grid-cols-2 gap-3 border-t border-admin-border pt-4 dark:border-admin-dark-border">
                        <div>
                            <dt className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                Pesanan
                            </dt>
                            <dd className="text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                {customer.orders}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                Total belanja
                            </dt>
                            <dd className="text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                {money(customer.spent)}
                            </dd>
                        </div>
                    </dl>
                </Card>

                <Card title="Riwayat Pesanan" bodyClassName="p-0" className="lg:col-span-2">
                    <Table
                        columns={[
                            { key: 'id', label: 'No. Pesanan' },
                            { key: 'date', label: 'Tanggal' },
                            { key: 'payment', label: 'Pembayaran' },
                            { key: 'total', label: 'Total', align: 'right' },
                            { key: 'status', label: 'Status' },
                        ]}
                        rows={orders}
                        rowKey={(row) => row.id}
                        empty="Pelanggan ini belum pernah memesan."
                        renderCell={(row, key) => {
                            if (key === 'total') {
                                return (
                                    <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                        {money(row.total)}
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
