import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Table from '@/Components/Admin/Table';
import { customers, money, orders, statusTone } from '@/Components/Admin/data';

const customer = customers[0];

export default function CustomerDetail() {
    return (
        <AdminLayout
            title="Detail Pelanggan"
            heading={customer.name}
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pelanggan', href: '/admin/pelanggan' },
                { label: 'Detail' },
            ]}
            actions={
                <Button href="/admin/pelanggan/ubah" size="sm" icon="solar:pen-2-broken">
                    Ubah
                </Button>
            }
        >
            <div className="grid gap-5 lg:grid-cols-3">
                <Card>
                    <div className="text-center">
                        <img
                            src={customer.avatar}
                            alt={customer.name}
                            className="mx-auto h-20 w-20 rounded-full object-cover"
                        />
                        <h2 className="mt-3 text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                            {customer.name}
                        </h2>
                        <p className="text-xs text-admin-muted dark:text-admin-dark-muted">{customer.email}</p>
                        <Badge tone={statusTone(customer.status)} className="mt-2">
                            {customer.status}
                        </Badge>
                    </div>

                    <dl className="mt-5 space-y-3 border-t border-admin-border pt-5 text-[13px] dark:border-admin-dark-border">
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Telepon</dt>
                            <dd>{customer.phone}</dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Kota</dt>
                            <dd>{customer.city}</dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Total pesanan</dt>
                            <dd className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                {customer.orders}
                            </dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Total belanja</dt>
                            <dd className="font-semibold text-admin-heading dark:text-admin-dark-heading">
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
