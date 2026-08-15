import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import { money, products } from '@/Components/Admin/data';

export default function OrderCart() {
    const [lines, setLines] = useState(
        products.slice(0, 3).map((product) => ({ ...product, qty: 2 })),
    );

    const change = (id, delta) =>
        setLines((current) =>
            current
                .map((line) => (line.id === id ? { ...line, qty: line.qty + delta } : line))
                .filter((line) => line.qty > 0),
        );

    const subtotal = lines.reduce((total, line) => total + line.price * line.qty, 0);
    const shipping = lines.length ? 25000 : 0;

    return (
        <AdminLayout
            title="Keranjang"
            heading="Keranjang"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pesanan', href: '/admin/pesanan' },
                { label: 'Keranjang' },
            ]}
        >
            <div className="grid gap-5 lg:grid-cols-3">
                <Card title={`Item (${lines.length})`} bodyClassName="p-0" className="lg:col-span-2">
                    {lines.length === 0 ? (
                        <p className="px-5 py-12 text-center text-[13px] text-admin-muted dark:text-admin-dark-muted">
                            Keranjang kosong.
                        </p>
                    ) : (
                        <ul className="divide-y divide-admin-border dark:divide-admin-dark-border">
                            {lines.map((line) => (
                                <li key={line.id} className="flex items-center gap-4 px-5 py-4">
                                    <img
                                        src={line.image}
                                        alt=""
                                        className="h-14 w-14 shrink-0 rounded-lg bg-admin-hover object-contain p-1.5 dark:bg-admin-dark-hover"
                                    />

                                    <div className="min-w-0 flex-1">
                                        <p className="truncate text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                            {line.name}
                                        </p>
                                        <p className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                            {line.category}
                                        </p>
                                    </div>

                                    <div className="flex items-center gap-1">
                                        <button
                                            type="button"
                                            onClick={() => change(line.id, -1)}
                                            aria-label={`Kurangi ${line.name}`}
                                            className="flex h-8 w-8 items-center justify-center rounded-md border border-admin-border text-admin-body dark:border-admin-dark-border dark:text-admin-dark-body"
                                        >
                                            −
                                        </button>
                                        <span className="w-8 text-center text-[13px]">{line.qty}</span>
                                        <button
                                            type="button"
                                            onClick={() => change(line.id, 1)}
                                            aria-label={`Tambah ${line.name}`}
                                            className="flex h-8 w-8 items-center justify-center rounded-md border border-admin-border text-admin-body dark:border-admin-dark-border dark:text-admin-dark-body"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <span className="w-28 text-right text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                        {money(line.price * line.qty)}
                                    </span>

                                    <button
                                        type="button"
                                        onClick={() => change(line.id, -line.qty)}
                                        aria-label={`Hapus ${line.name}`}
                                        className="flex h-8 w-8 items-center justify-center rounded-md text-danger hover:bg-admin-hover dark:hover:bg-admin-dark-hover"
                                    >
                                        <Icon name="solar:trash-bin-minimalistic-2-broken" size={17} />
                                    </button>
                                </li>
                            ))}
                        </ul>
                    )}
                </Card>

                <Card title="Ringkasan">
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

                    <Button
                        href="/admin/pesanan/checkout"
                        className="mt-5 w-full"
                        disabled={lines.length === 0}
                    >
                        Lanjut ke Checkout
                    </Button>
                </Card>
            </div>
        </AdminLayout>
    );
}
