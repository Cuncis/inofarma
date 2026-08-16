import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import { Field, Input, Select, Textarea } from '@/Components/Admin/Form';
import { useCatalog } from '@/lib/catalog';
import { money } from '@/Components/Admin/data';

const suppliers = ['PT Kimia Farma', 'PT Kalbe Farma', 'PT Dexa Medica', 'PT Bio Farma'];

export default function PurchaseOrder() {
    const { products } = useCatalog();

    const [lines, setLines] = useState(() => [
        { id: 1, product: products[0]?.name ?? '', qty: 100, price: 9000 },
        { id: 2, product: products[1]?.name ?? '', qty: 50, price: 28000 },
    ]);

    const update = (id, patch) =>
        setLines((current) =>
            current.map((line) => (line.id === id ? { ...line, ...patch } : line)),
        );

    const addLine = () =>
        setLines((current) => [
            ...current,
            { id: Math.max(0, ...current.map((line) => line.id)) + 1, product: products[0]?.name ?? '', qty: 1, price: 0 },
        ]);

    const total = lines.reduce((sum, line) => sum + line.qty * line.price, 0);

    const submit = (event) => {
        event.preventDefault();

        router.visit('/admin/pembelian');
    };

    return (
        <AdminLayout
            title="Buat Order Pembelian"
            heading="Order Pembelian"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pembelian', href: '/admin/pembelian' },
                { label: 'Order' },
            ]}
        >
            <form onSubmit={submit} className="space-y-5">
                <Card title="Informasi Order">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label="Pemasok" htmlFor="supplier">
                            <Select id="supplier" name="supplier" options={suppliers} />
                        </Field>

                        <Field label="Nomor PO" htmlFor="number">
                            <Input id="number" name="number" defaultValue="PO-1044" />
                        </Field>

                        <Field label="Tanggal Order" htmlFor="date">
                            <Input id="date" name="date" type="date" />
                        </Field>

                        <Field label="Gudang Tujuan" htmlFor="warehouse">
                            <Select
                                id="warehouse"
                                name="warehouse"
                                options={['Gudang Jakarta', 'Gudang Bandung', 'Gudang Surabaya', 'Gudang Medan']}
                            />
                        </Field>
                    </div>
                </Card>

                <Card title="Item Pembelian" bodyClassName="p-0">
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[640px] border-collapse">
                            <thead>
                                <tr className="border-b border-admin-border dark:border-admin-dark-border">
                                    {['Produk', 'Jumlah', 'Harga Satuan', 'Subtotal', ''].map((label) => (
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
                                {lines.map((line) => (
                                    <tr
                                        key={line.id}
                                        className="border-b border-admin-border last:border-0 dark:border-admin-dark-border"
                                    >
                                        <td className="px-4 py-2.5">
                                            <Select
                                                value={line.product}
                                                onChange={(event) => update(line.id, { product: event.target.value })}
                                                options={products.map((item) => item.name)}
                                                aria-label="Produk"
                                            />
                                        </td>
                                        <td className="px-4 py-2.5">
                                            <Input
                                                type="number"
                                                min="1"
                                                value={line.qty}
                                                onChange={(event) => update(line.id, { qty: Number(event.target.value) })}
                                                aria-label="Jumlah"
                                                className="w-24"
                                            />
                                        </td>
                                        <td className="px-4 py-2.5">
                                            <Input
                                                type="number"
                                                min="0"
                                                value={line.price}
                                                onChange={(event) => update(line.id, { price: Number(event.target.value) })}
                                                aria-label="Harga satuan"
                                                className="w-32"
                                            />
                                        </td>
                                        <td className="px-4 py-2.5 text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                            {money(line.qty * line.price)}
                                        </td>
                                        <td className="px-4 py-2.5 text-right">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setLines((current) => current.filter((item) => item.id !== line.id))
                                                }
                                                aria-label={`Hapus baris ${line.product}`}
                                                className="flex h-8 w-8 items-center justify-center rounded-md text-danger hover:bg-admin-hover dark:hover:bg-admin-dark-hover"
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
                        <Button type="button" onClick={addLine} variant="outline" size="sm" icon="solar:add-circle-broken">
                            Tambah Baris
                        </Button>

                        <p className="text-[15px] font-bold text-admin-heading dark:text-admin-dark-heading">
                            Total: {money(total)}
                        </p>
                    </div>
                </Card>

                <Card title="Catatan">
                    <Textarea name="notes" placeholder="Catatan untuk pemasok..." />

                    <div className="mt-5 flex gap-2 border-t border-admin-border pt-5 dark:border-admin-dark-border">
                        <Button type="submit">Simpan Order</Button>
                        <Button href="/admin/pembelian" variant="outline">
                            Batal
                        </Button>
                    </div>
                </Card>
            </form>
        </AdminLayout>
    );
}
