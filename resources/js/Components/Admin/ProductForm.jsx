import { useForm } from '@inertiajs/react';
import Button from './Button';
import Card from './Card';
import { Field, Input, Select, Switch, Textarea } from './Form';
import { money } from './data';

/**
 * Create/update form for a product, shared by the add and edit screens.
 *
 * Submits through Inertia's `useForm`, so validation errors come back from the
 * server and render against the field that produced them.
 *
 * @param {{
 *   product?: object,
 *   categories: string[],
 *   units: string[],
 *   statuses: string[],
 *   submitLabel: string,
 * }} props
 */
export default function ProductForm({ product, categories, units, statuses, submitLabel }) {
    const editing = Boolean(product);

    const { data, setData, post, put, processing, errors, isDirty } = useForm({
        name: product?.name ?? '',
        category: product?.category ?? categories[0],
        unit: product?.unit ?? units[0],
        status: product?.status ?? statuses[0],
        price: product?.price ?? '',
        oldPrice: product?.oldPrice ?? '',
        stock: product?.stock ?? '',
        prescription: product?.prescription ?? false,
        blurb: product?.blurb ?? '',
    });

    const submit = (event) => {
        event.preventDefault();

        const options = { preserveScroll: true };

        if (editing) {
            put(`/admin/produk/${product.id}`, options);
        } else {
            post('/admin/produk', options);
        }
    };

    const margin =
        Number(data.oldPrice) > Number(data.price) && Number(data.price) > 0
            ? Math.round(((data.oldPrice - data.price) / data.oldPrice) * 100)
            : null;

    return (
        <form onSubmit={submit} className="grid gap-5 lg:grid-cols-3">
            <div className="space-y-5 lg:col-span-2">
                <Card title="Informasi Produk">
                    <div className="space-y-4">
                        <Field label="Nama Produk" htmlFor="name" hint={errors.name}>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(event) => setData('name', event.target.value)}
                                placeholder="Paracetamol 500mg"
                                aria-invalid={Boolean(errors.name)}
                                className={errors.name ? 'border-danger' : ''}
                            />
                        </Field>

                        <Field
                            label="Deskripsi"
                            htmlFor="blurb"
                            hint={errors.blurb ?? 'Jelaskan indikasi dan aturan pakai.'}
                        >
                            <Textarea
                                id="blurb"
                                value={data.blurb}
                                onChange={(event) => setData('blurb', event.target.value)}
                                placeholder="Tuliskan deskripsi produk..."
                                className={errors.blurb ? 'border-danger' : ''}
                            />
                        </Field>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label="Kategori" htmlFor="category" hint={errors.category}>
                                <Select
                                    id="category"
                                    value={data.category}
                                    onChange={(event) => setData('category', event.target.value)}
                                    options={categories}
                                />
                            </Field>

                            <Field label="Satuan" htmlFor="unit" hint={errors.unit}>
                                <Select
                                    id="unit"
                                    value={data.unit}
                                    onChange={(event) => setData('unit', event.target.value)}
                                    options={units}
                                />
                            </Field>
                        </div>
                    </div>
                </Card>

                <Card title="Harga & Stok">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field
                            label="Harga Jual (Rp)"
                            htmlFor="price"
                            hint={errors.price ?? (data.price ? money(Number(data.price)) : undefined)}
                        >
                            <Input
                                id="price"
                                type="number"
                                min="0"
                                value={data.price}
                                onChange={(event) => setData('price', event.target.value)}
                                placeholder="12500"
                                className={errors.price ? 'border-danger' : ''}
                            />
                        </Field>

                        <Field
                            label="Harga Coret (Rp)"
                            htmlFor="oldPrice"
                            hint={
                                errors.oldPrice ??
                                (margin ? `Tampil sebagai diskon ${margin}%` : 'Kosongkan bila tidak diskon')
                            }
                        >
                            <Input
                                id="oldPrice"
                                type="number"
                                min="0"
                                value={data.oldPrice ?? ''}
                                onChange={(event) => setData('oldPrice', event.target.value)}
                                placeholder="15000"
                                className={errors.oldPrice ? 'border-danger' : ''}
                            />
                        </Field>

                        <Field label="Stok" htmlFor="stock" hint={errors.stock}>
                            <Input
                                id="stock"
                                type="number"
                                min="0"
                                value={data.stock}
                                onChange={(event) => setData('stock', event.target.value)}
                                placeholder="100"
                                className={errors.stock ? 'border-danger' : ''}
                            />
                        </Field>

                        <Field label="SKU" htmlFor="sku" hint="Dibuat otomatis">
                            <Input id="sku" value={product?.id ?? 'Otomatis'} disabled readOnly />
                        </Field>
                    </div>
                </Card>
            </div>

            <div className="space-y-5">
                <Card title="Publikasi">
                    <div className="space-y-4">
                        <Field label="Status Stok" htmlFor="status" hint={errors.status}>
                            <Select
                                id="status"
                                value={data.status}
                                onChange={(event) => setData('status', event.target.value)}
                                options={statuses}
                            />
                        </Field>

                        <Switch
                            checked={data.prescription}
                            onChange={() => setData('prescription', ! data.prescription)}
                            label={data.prescription ? 'Butuh resep dokter' : 'Bebas tanpa resep'}
                        />
                    </div>
                </Card>

                <div className="flex gap-2">
                    <Button type="submit" disabled={processing} className="flex-1">
                        {processing ? 'Menyimpan…' : submitLabel}
                    </Button>
                    <Button href="/admin/produk" variant="outline">
                        Batal
                    </Button>
                </div>

                {isDirty ? (
                    <p className="text-center text-xs text-admin-muted dark:text-admin-dark-muted">
                        Ada perubahan yang belum disimpan.
                    </p>
                ) : null}
            </div>
        </form>
    );
}
