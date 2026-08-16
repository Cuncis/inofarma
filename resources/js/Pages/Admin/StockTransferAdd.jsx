import { useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import { Field, Input, Select, Textarea } from '@/Components/Admin/Form';

export default function StockTransferAdd({ branches, products }) {
    const { data, setData, post, processing, errors } = useForm({
        fromBranch: branches[0]?.id ?? '',
        toBranch: branches[1]?.id ?? branches[0]?.id ?? '',
        product: products[0]?.id ?? '',
        quantity: 1,
        note: '',
    });

    const submit = (event) => {
        event.preventDefault();

        post('/admin/inventaris/transfer', { preserveScroll: true });
    };

    return (
        <AdminLayout
            title="Buat Transfer Stok"
            heading="Buat Transfer Stok"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Inventaris' },
                { label: 'Transfer Stok', href: '/admin/inventaris/transfer' },
                { label: 'Buat' },
            ]}
        >
            <form onSubmit={submit} className="mx-auto max-w-2xl">
                <Card title="Permintaan Transfer">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label="Cabang Asal" htmlFor="fromBranch" hint={errors.fromBranch}>
                            <Select
                                id="fromBranch"
                                value={data.fromBranch}
                                onChange={(event) => setData('fromBranch', event.target.value)}
                                options={branches.map((branch) => ({ value: branch.id, label: branch.name }))}
                            />
                        </Field>

                        <Field label="Cabang Tujuan" htmlFor="toBranch" hint={errors.toBranch}>
                            <Select
                                id="toBranch"
                                value={data.toBranch}
                                onChange={(event) => setData('toBranch', event.target.value)}
                                options={branches.map((branch) => ({ value: branch.id, label: branch.name }))}
                            />
                        </Field>

                        <Field
                            label="Produk"
                            htmlFor="product"
                            hint={errors.product}
                            className="sm:col-span-2"
                        >
                            <Select
                                id="product"
                                value={data.product}
                                onChange={(event) => setData('product', event.target.value)}
                                options={products.map((product) => ({ value: product.id, label: product.name }))}
                            />
                        </Field>

                        <Field label="Jumlah" htmlFor="quantity" hint={errors.quantity}>
                            <Input
                                id="quantity"
                                type="number"
                                min="1"
                                value={data.quantity}
                                onChange={(event) => setData('quantity', event.target.value)}
                                className={errors.quantity ? 'border-danger' : ''}
                            />
                        </Field>

                        <div className="hidden sm:block" />

                        <Field
                            label="Catatan"
                            htmlFor="note"
                            hint={errors.note}
                            className="sm:col-span-2"
                        >
                            <Textarea
                                id="note"
                                value={data.note}
                                onChange={(event) => setData('note', event.target.value)}
                                placeholder="Alasan permintaan, opsional"
                            />
                        </Field>
                    </div>

                    <div className="mt-6 flex gap-2 border-t border-admin-border pt-5 dark:border-admin-dark-border">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan…' : 'Kirim Permintaan'}
                        </Button>
                        <Button href="/admin/inventaris/transfer" variant="outline">
                            Batal
                        </Button>
                    </div>
                </Card>
            </form>
        </AdminLayout>
    );
}
