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
 *   sellers: string[],
 *   units: string[],
 *   statuses: string[],
 *   submitLabel: string,
 * }} props
 */
export default function ProductForm({
    product,
    categories,
    sellers,
    units,
    statuses,
    submitLabel,
}) {
    const editing = Boolean(product);

    const { data, setData, post, put, processing, errors, isDirty } = useForm({
        name: product?.name ?? '',
        category: product?.category ?? categories[0],
        seller: product?.seller ?? sellers[0],
        unit: product?.unit ?? units[0],
        status: product?.status ?? statuses[0],
        price: product?.price ?? '',
        oldPrice: product?.oldPrice ?? '',
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

                            <Field
                                label="Penjual"
                                htmlFor="seller"
                                hint={errors.seller}
                                className="sm:col-span-2"
                            >
                                <Select
                                    id="seller"
                                    value={data.seller}
                                    onChange={(event) => setData('seller', event.target.value)}
                                    options={sellers}
                                />
                            </Field>
                        </div>
                    </div>
                </Card>

                <Card title="Harga">
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

                        <Field label="SKU" htmlFor="sku" hint="Dibuat otomatis">
                            <Input id="sku" value={product?.id ?? 'Otomatis'} disabled readOnly />
                        </Field>

                        <Field label="Satuan Jual" htmlFor="unit-echo" hint="Sama dengan pilihan di atas">
                            <Input id="unit-echo" value={data.unit} disabled readOnly />
                        </Field>
                    </div>

                    {/*
                        Stok tidak ada di sini dengan sengaja: satu produk punya
                        stok berbeda di setiap cabang, jadi satu kotak isian tidak
                        bisa menjawab "stok di cabang yang mana".
                    */}
                    <p className="mt-4 rounded-lg bg-admin-hover px-3 py-2.5 text-xs leading-relaxed text-admin-body dark:bg-admin-dark-hover dark:text-admin-dark-body">
                        Stok diatur per cabang, bukan di sini.{' '}
                        {editing ? (
                            <>
                                Lihat sebarannya di{' '}
                                <a href={`/admin/produk/${product.id}`} className="font-semibold underline">
                                    detail produk
                                </a>
                                .
                            </>
                        ) : (
                            'Setelah produk dibuat, atur stok awal dari halaman cabang.'
                        )}
                    </p>
                </Card>
            </div>

            <div className="space-y-5">
                <Card title="Publikasi">
                    <div className="space-y-4">
                        <Field label="Status Produk" htmlFor="status" hint={errors.status}>
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
