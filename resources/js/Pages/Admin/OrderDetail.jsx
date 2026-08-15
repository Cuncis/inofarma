import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import Table from '@/Components/Admin/Table';
import { invoiceLines, money, orders, statusTone } from '@/Components/Admin/data';

const order = orders[0];
const subtotal = invoiceLines.reduce((total, line) => total + line.qty * line.price, 0);
const shipping = 25000;

const timeline = [
    { label: 'Pesanan dibuat', at: '14 Agu 2025, 09.00 WIB', done: true },
    { label: 'Pembayaran diterima', at: '14 Agu 2025, 09.12 WIB', done: true },
    { label: 'Sedang disiapkan', at: '14 Agu 2025, 11.30 WIB', done: true },
    { label: 'Dikirim', at: '15 Agu 2025, 08.00 WIB', done: false },
    { label: 'Diterima', at: 'Menunggu', done: false },
];

export default function OrderDetail() {
    return (
        <AdminLayout
            title="Detail Pesanan"
            heading={`Pesanan ${order.id}`}
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pesanan', href: '/admin/pesanan' },
                { label: 'Detail' },
            ]}
            actions={
                <>
                    <Button variant="outline" size="sm" icon="solar:printer-broken">
                        Cetak
                    </Button>
                    <Button href="/admin/faktur/detail" size="sm">
                        Lihat Faktur
                    </Button>
                </>
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
                            rows={invoiceLines}
                            rowKey={(row) => row.name}
                            renderCell={(row, key) => {
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
                                <span>{money(subtotal)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-admin-muted dark:text-admin-dark-muted">Ongkos kirim</span>
                                <span>{money(shipping)}</span>
                            </div>
                            <div className="flex justify-between border-t border-admin-border pt-2 text-[15px] font-bold text-admin-heading dark:border-admin-dark-border dark:text-admin-dark-heading">
                                <span>Total</span>
                                <span>{money(subtotal + shipping)}</span>
                            </div>
                        </div>
                    </Card>

                    <Card title="Status Pengiriman">
                        <ol className="relative space-y-6 border-l border-admin-border pl-6 dark:border-admin-dark-border">
                            {timeline.map((step) => (
                                <li key={step.label} className="relative">
                                    <span
                                        className={`absolute -left-[31px] flex h-5 w-5 items-center justify-center rounded-full ${
                                            step.done
                                                ? 'bg-brand text-white'
                                                : 'border-2 border-admin-border bg-admin-card dark:border-admin-dark-border dark:bg-admin-dark-card'
                                        }`}
                                    >
                                        {step.done ? <Icon name="solar:check-circle-broken" size={12} /> : null}
                                    </span>

                                    <p
                                        className={`text-[13px] font-semibold ${
                                            step.done
                                                ? 'text-admin-heading dark:text-admin-dark-heading'
                                                : 'text-admin-muted dark:text-admin-dark-muted'
                                        }`}
                                    >
                                        {step.label}
                                    </p>
                                    <p className="text-xs text-admin-muted dark:text-admin-dark-muted">{step.at}</p>
                                </li>
                            ))}
                        </ol>
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
                        </dl>
                    </Card>

                    <Card title="Pelanggan">
                        <div className="flex items-center gap-3">
                            <img src={order.avatar} alt="" className="h-11 w-11 rounded-full object-cover" />
                            <div>
                                <p className="text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {order.customer}
                                </p>
                                <p className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                    kirana.wijaya@mail.com
                                </p>
                            </div>
                        </div>

                        <div className="mt-4 border-t border-admin-border pt-4 dark:border-admin-dark-border">
                            <p className="text-xs text-admin-muted dark:text-admin-dark-muted">Alamat pengiriman</p>
                            <p className="mt-1 text-[13px] leading-relaxed text-admin-body dark:text-admin-dark-body">
                                Jl. Kebon Jeruk Raya No. 27
                                <br />
                                Jakarta Barat 11530
                            </p>
                        </div>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    );
}
