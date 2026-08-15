import { useForm } from '@inertiajs/react';
import Button from './Button';
import Card from './Card';
import Icon from './Icon';
import { Field, Input, Select, Textarea } from './Form';
import { money } from './data';

/**
 * Create/update form for an order, shared by the add and edit screens.
 *
 * Line items are editable rows: only the product and quantity are submitted —
 * the server snapshots the name and price from the catalogue, so the browser
 * cannot dictate what an order was charged.
 *
 * @param {{
 *   order?: object,
 *   customers: { email: string, name: string }[],
 *   products: { id: string, name: string, price: number }[],
 *   statuses: string[],
 *   payments: string[],
 *   submitLabel: string,
 * }} props
 */
export default function OrderForm({
    order,
    customers,
    products,
    statuses,
    payments,
    submitLabel,
}) {
    const editing = Boolean(order);

    const { data, setData, post, put, processing, errors } = useForm({
        customerEmail: order?.customerEmail ?? customers[0]?.email ?? '',
        payment: order?.payment ?? payments[0],
        status: order?.status ?? statuses[0],
        shipping: order?.shipping ?? 0,
        note: order?.note ?? '',
        items: order?.items?.map((item) => ({ productId: item.productId, qty: item.qty })) ?? [
            { productId: products[0]?.id ?? '', qty: 1 },
        ],
    });

    const priceOf = (id) => products.find((product) => product.id === id)?.price ?? 0;

    const setItem = (index, patch) =>
        setData(
            'items',
            data.items.map((item, at) => (at === index ? { ...item, ...patch } : item)),
        );

    const subtotal = data.items.reduce(
        (sum, item) => sum + priceOf(item.productId) * Number(item.qty || 0),
        0,
    );

    const submit = (event) => {
        event.preventDefault();

        const options = { preserveScroll: true };

        if (editing) {
            put(`/admin/pesanan/${order.id}`, options);
        } else {
            post('/admin/pesanan', options);
        }
    };

    return (
        <form onSubmit={submit} className="grid gap-5 lg:grid-cols-3">
            <div className="space-y-5 lg:col-span-2">
                <Card title="Item Pesanan" bodyClassName="p-0">
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[560px] border-collapse">
                            <thead>
                                <tr className="border-b border-admin-border dark:border-admin-dark-border">
                                    {['Produk', 'Jumlah', 'Harga', 'Subtotal', ''].map((label) => (
                                        <th
                                            key={label}
                                            className="px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-admin-muted dark:text-admin-dark-muted"
                                        >
                                            {label}
                                        </th>
                                    ))}
                                </tr>
                            </thead>

                            <tbody>
                                {data.items.map((item, index) => (
                                    <tr
                                        key={index}
                                        className="border-b border-admin-border last:border-0 dark:border-admin-dark-border"
                                    >
                                        <td className="px-4 py-2.5">
                                            <Select
                                                value={item.productId}
                                                onChange={(event) =>
                                                    setItem(index, { productId: event.target.value })
                                                }
                                                options={products.map((product) => ({
                                                    value: product.id,
                                                    label: product.name,
                                                }))}
                                                aria-label={`Produk baris ${index + 1}`}
                                            />
                                            {errors[`items.${index}.productId`] ? (
                                                <p className="mt-1 text-xs text-danger">
                                                    {errors[`items.${index}.productId`]}
                                                </p>
                                            ) : null}
                                        </td>

                                        <td className="px-4 py-2.5">
                                            <Input
                                                type="number"
                                                min="1"
                                                value={item.qty}
                                                onChange={(event) =>
                                                    setItem(index, { qty: event.target.value })
                                                }
                                                aria-label={`Jumlah baris ${index + 1}`}
                                                className="w-24"
                                            />
                                            {errors[`items.${index}.qty`] ? (
                                                <p className="mt-1 text-xs text-danger">
                                                    {errors[`items.${index}.qty`]}
                                                </p>
                                            ) : null}
                                        </td>

                                        <td className="whitespace-nowrap px-4 py-2.5 text-[13px] text-admin-body dark:text-admin-dark-body">
                                            {money(priceOf(item.productId))}
                                        </td>

                                        <td className="whitespace-nowrap px-4 py-2.5 text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                            {money(priceOf(item.productId) * Number(item.qty || 0))}
                                        </td>

                                        <td className="px-4 py-2.5 text-right">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setData(
                                                        'items',
                                                        data.items.filter((_, at) => at !== index),
                                                    )
                                                }
                                                disabled={data.items.length === 1}
                                                aria-label={`Hapus baris ${index + 1}`}
                                                className="flex h-8 w-8 items-center justify-center rounded-md text-danger hover:bg-admin-hover disabled:opacity-40 dark:hover:bg-admin-dark-hover"
                                            >
                                                <Icon name="solar:trash-bin-minimalistic-2-broken" size={17} />
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="flex flex-wrap items-center justify-between gap-3 border-t border-admin-border px-5 py-4 dark:border-admin-dark-border">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            icon="solar:add-circle-broken"
                            onClick={() =>
                                setData('items', [
                                    ...data.items,
                                    { productId: products[0]?.id ?? '', qty: 1 },
                                ])
                            }
                        >
                            Tambah Item
                        </Button>

                        <p className="text-[13px] text-admin-body dark:text-admin-dark-body">
                            Subtotal:{' '}
                            <span className="font-bold text-admin-heading dark:text-admin-dark-heading">
                                {money(subtotal)}
                            </span>
                        </p>
                    </div>

                    {errors.items ? (
                        <p className="border-t border-admin-border px-5 py-2.5 text-xs text-danger dark:border-admin-dark-border">
                            {errors.items}
                        </p>
                    ) : null}
                </Card>

                <Card title="Catatan">
                    <Textarea
                        value={data.note}
                        onChange={(event) => setData('note', event.target.value)}
                        placeholder="Catatan untuk kurir atau pelanggan..."
                        className={errors.note ? 'border-danger' : ''}
                    />
                </Card>
            </div>

            <div className="space-y-5">
                <Card title="Informasi Pesanan">
                    <div className="space-y-4">
                        <Field label="Pelanggan" htmlFor="customerEmail" hint={errors.customerEmail}>
                            <Select
                                id="customerEmail"
                                value={data.customerEmail}
                                onChange={(event) => setData('customerEmail', event.target.value)}
                                options={customers.map((customer) => ({
                                    value: customer.email,
                                    label: `${customer.name} — ${customer.email}`,
                                }))}
                            />
                        </Field>

                        <Field label="Metode Pembayaran" htmlFor="payment" hint={errors.payment}>
                            <Select
                                id="payment"
                                value={data.payment}
                                onChange={(event) => setData('payment', event.target.value)}
                                options={payments}
                            />
                        </Field>

                        <Field label="Status" htmlFor="status" hint={errors.status}>
                            <Select
                                id="status"
                                value={data.status}
                                onChange={(event) => setData('status', event.target.value)}
                                options={statuses}
                            />
                        </Field>

                        <Field label="Ongkos Kirim (Rp)" htmlFor="shipping" hint={errors.shipping}>
                            <Input
                                id="shipping"
                                type="number"
                                min="0"
                                value={data.shipping}
                                onChange={(event) => setData('shipping', event.target.value)}
                                className={errors.shipping ? 'border-danger' : ''}
                            />
                        </Field>
                    </div>

                    <dl className="mt-5 space-y-2 border-t border-admin-border pt-4 text-[13px] dark:border-admin-dark-border">
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Subtotal</dt>
                            <dd>{money(subtotal)}</dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Ongkos kirim</dt>
                            <dd>{money(Number(data.shipping || 0))}</dd>
                        </div>
                        <div className="flex justify-between border-t border-admin-border pt-2 text-[15px] font-bold text-admin-heading dark:border-admin-dark-border dark:text-admin-dark-heading">
                            <dt>Total</dt>
                            <dd>{money(subtotal + Number(data.shipping || 0))}</dd>
                        </div>
                    </dl>
                </Card>

                <div className="flex gap-2">
                    <Button type="submit" disabled={processing} className="flex-1">
                        {processing ? 'Menyimpan…' : submitLabel}
                    </Button>
                    <Button href="/admin/pesanan" variant="outline">
                        Batal
                    </Button>
                </div>
            </div>
        </form>
    );
}
