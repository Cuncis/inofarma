import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import { Field, Input, Select, Textarea } from '@/Components/Admin/Form';
import { invoiceLines, money } from '@/Components/Admin/data';

const methods = [
    { key: 'transfer', label: 'Transfer Bank', icon: 'solar:card-send-bold-duotone' },
    { key: 'gopay', label: 'GoPay', icon: 'solar:wallet-money-bold-duotone' },
    { key: 'ovo', label: 'OVO', icon: 'solar:wallet-money-bold-duotone' },
    { key: 'tunai', label: 'Tunai', icon: 'solar:bill-list-bold-duotone' },
];

const subtotal = invoiceLines.reduce((total, line) => total + line.qty * line.price, 0);
const shipping = 25000;

export default function OrderCheckout() {
    const [method, setMethod] = useState('transfer');
    const [placing, setPlacing] = useState(false);

    const submit = (event) => {
        event.preventDefault();
        setPlacing(true);

        window.setTimeout(() => router.visit('/admin/pesanan'), 600);
    };

    return (
        <AdminLayout
            title="Checkout"
            heading="Checkout"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pesanan', href: '/admin/pesanan' },
                { label: 'Checkout' },
            ]}
        >
            <form onSubmit={submit} className="grid gap-5 lg:grid-cols-3">
                <div className="space-y-5 lg:col-span-2">
                    <Card title="Alamat Pengiriman">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label="Nama Penerima" htmlFor="name">
                                <Input id="name" name="name" defaultValue="Kirana Wijaya" />
                            </Field>

                            <Field label="Nomor Telepon" htmlFor="phone">
                                <Input id="phone" name="phone" type="tel" defaultValue="+62 812-3456-7890" />
                            </Field>

                            <Field label="Kota" htmlFor="city">
                                <Input id="city" name="city" defaultValue="Jakarta Barat" />
                            </Field>

                            <Field label="Kode Pos" htmlFor="zip">
                                <Input id="zip" name="zip" defaultValue="11530" />
                            </Field>

                            <Field label="Alamat Lengkap" htmlFor="address" className="sm:col-span-2">
                                <Textarea
                                    id="address"
                                    name="address"
                                    defaultValue="Jl. Kebon Jeruk Raya No. 27"
                                />
                            </Field>
                        </div>
                    </Card>

                    <Card title="Metode Pembayaran">
                        <div className="grid gap-3 sm:grid-cols-2">
                            {methods.map((option) => (
                                <button
                                    key={option.key}
                                    type="button"
                                    onClick={() => setMethod(option.key)}
                                    className={`flex items-center gap-3 rounded-lg border p-4 text-left transition-colors ${
                                        method === option.key
                                            ? 'border-brand bg-blush dark:bg-brand/20'
                                            : 'border-admin-border hover:bg-admin-hover dark:border-admin-dark-border dark:hover:bg-admin-dark-hover'
                                    }`}
                                >
                                    <Icon
                                        name={option.icon}
                                        size={24}
                                        className={method === option.key ? 'text-brand' : 'text-admin-muted'}
                                    />
                                    <span className="flex-1 text-[13px] font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {option.label}
                                    </span>
                                    <span
                                        className={`h-4 w-4 rounded-full border-2 ${
                                            method === option.key
                                                ? 'border-[5px] border-brand'
                                                : 'border-admin-border dark:border-admin-dark-border'
                                        }`}
                                    />
                                </button>
                            ))}
                        </div>
                    </Card>

                    <Card title="Pengiriman">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label="Kurir" htmlFor="courier">
                                <Select id="courier" name="courier" options={['JNE', 'J&T Express', 'SiCepat', 'AnterAja']} />
                            </Field>

                            <Field label="Layanan" htmlFor="service">
                                <Select id="service" name="service" options={['Reguler', 'Express', 'Same Day']} />
                            </Field>

                            <Field label="Catatan" htmlFor="notes" className="sm:col-span-2">
                                <Textarea id="notes" name="notes" placeholder="Catatan untuk kurir (opsional)..." />
                            </Field>
                        </div>
                    </Card>
                </div>

                <Card title="Ringkasan Pesanan">
                    <ul className="mb-4 space-y-2.5 border-b border-admin-border pb-4 dark:border-admin-dark-border">
                        {invoiceLines.map((line) => (
                            <li key={line.name} className="flex justify-between gap-2 text-[13px]">
                                <span className="min-w-0 truncate text-admin-body dark:text-admin-dark-body">
                                    {line.name} × {line.qty}
                                </span>
                                <span className="shrink-0 font-medium text-admin-heading dark:text-admin-dark-heading">
                                    {money(line.qty * line.price)}
                                </span>
                            </li>
                        ))}
                    </ul>

                    <dl className="space-y-2.5 text-[13px]">
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Subtotal</dt>
                            <dd>{money(subtotal)}</dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Ongkos kirim</dt>
                            <dd>{money(shipping)}</dd>
                        </div>
                        <div className="flex justify-between border-t border-admin-border pt-2.5 text-[15px] font-bold text-admin-heading dark:border-admin-dark-border dark:text-admin-dark-heading">
                            <dt>Total</dt>
                            <dd>{money(subtotal + shipping)}</dd>
                        </div>
                    </dl>

                    <Button type="submit" disabled={placing} className="mt-5 w-full">
                        {placing ? 'Memproses…' : 'Buat Pesanan'}
                    </Button>
                </Card>
            </form>
        </AdminLayout>
    );
}
