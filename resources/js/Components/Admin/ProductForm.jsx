import { useForm } from '@inertiajs/react';
import { drugWarnings } from '@/lib/drugWarnings';
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
    drugClasses,
    storageConditions,
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
        drugClass: product?.drugClass ?? drugClasses[0],
        nie: product?.nie ?? '',
        composition: product?.composition ?? '',
        indication: product?.indication ?? '',
        dosage: product?.dosage ?? '',
        sideEffects: product?.sideEffects ?? '',
        warning: product?.warning ?? '',
        manufacturer: product?.manufacturer ?? '',
        maxQtyPerOrder: product?.maxQtyPerOrder ?? '',
        storage: product?.storage ?? storageConditions[0],
        weightGrams: product?.weightGrams ?? '',
    });

    const needsWarning = data.drugClass === 'Bebas Terbatas';

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

                <Card title="Informasi Farmasi">
                    <div className="space-y-4">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label="Golongan Obat" htmlFor="drugClass" hint={errors.drugClass}>
                                <Select
                                    id="drugClass"
                                    value={data.drugClass}
                                    onChange={(event) => setData('drugClass', event.target.value)}
                                    options={drugClasses}
                                />
                            </Field>

                            <Field
                                label="Nomor Izin Edar (NIE BPOM)"
                                htmlFor="nie"
                                hint={errors.nie}
                            >
                                <Input
                                    id="nie"
                                    value={data.nie}
                                    onChange={(event) => setData('nie', event.target.value)}
                                    placeholder="DBL7812345678A1"
                                    className={errors.nie ? 'border-danger' : ''}
                                />
                            </Field>

                            <Field label="Komposisi" htmlFor="composition" hint={errors.composition} className="sm:col-span-2">
                                <Input
                                    id="composition"
                                    value={data.composition}
                                    onChange={(event) => setData('composition', event.target.value)}
                                    placeholder="Tiap tablet mengandung Paracetamol 500 mg"
                                    className={errors.composition ? 'border-danger' : ''}
                                />
                            </Field>

                            <Field label="Produsen" htmlFor="manufacturer" hint={errors.manufacturer}>
                                <Input
                                    id="manufacturer"
                                    value={data.manufacturer}
                                    onChange={(event) => setData('manufacturer', event.target.value)}
                                    placeholder="PT Kimia Farma Tbk"
                                    className={errors.manufacturer ? 'border-danger' : ''}
                                />
                            </Field>

                            <Field label="Kondisi Penyimpanan" htmlFor="storage" hint={errors.storage}>
                                <Select
                                    id="storage"
                                    value={data.storage}
                                    onChange={(event) => setData('storage', event.target.value)}
                                    options={storageConditions}
                                />
                            </Field>
                        </div>

                        <Field label="Indikasi" htmlFor="indication" hint={errors.indication}>
                            <Textarea
                                id="indication"
                                rows={2}
                                value={data.indication}
                                onChange={(event) => setData('indication', event.target.value)}
                                placeholder="Meredakan demam dan nyeri ringan hingga sedang."
                                className={errors.indication ? 'border-danger' : ''}
                            />
                        </Field>

                        <Field label="Aturan Pakai / Dosis" htmlFor="dosage" hint={errors.dosage}>
                            <Textarea
                                id="dosage"
                                rows={2}
                                value={data.dosage}
                                onChange={(event) => setData('dosage', event.target.value)}
                                placeholder="Dewasa: 1 tablet, 3 kali sehari setelah makan."
                                className={errors.dosage ? 'border-danger' : ''}
                            />
                        </Field>

                        <Field label="Efek Samping" htmlFor="sideEffects" hint={errors.sideEffects}>
                            <Textarea
                                id="sideEffects"
                                rows={2}
                                value={data.sideEffects}
                                onChange={(event) => setData('sideEffects', event.target.value)}
                                placeholder="Jarang: mual, ruam kulit."
                                className={errors.sideEffects ? 'border-danger' : ''}
                            />
                        </Field>

                        <Field
                            label="Peringatan"
                            htmlFor="warning"
                            hint={
                                errors.warning ??
                                (needsWarning
                                    ? 'Wajib diisi untuk obat bebas terbatas — pilih salah satu label P1–P6 di bawah, atau tulis sendiri.'
                                    : undefined)
                            }
                        >
                            {needsWarning ? (
                                <div className="mb-2 flex flex-wrap gap-1.5">
                                    {drugWarnings.map((item) => (
                                        <button
                                            key={item.code}
                                            type="button"
                                            onClick={() => setData('warning', item.text)}
                                            className="rounded-full border border-admin-border px-2.5 py-1 text-[11px] font-semibold text-admin-body hover:border-brand hover:text-brand dark:border-admin-dark-border dark:text-admin-dark-body"
                                        >
                                            {item.code}
                                        </button>
                                    ))}
                                </div>
                            ) : null}
                            <Textarea
                                id="warning"
                                rows={2}
                                value={data.warning}
                                onChange={(event) => setData('warning', event.target.value)}
                                placeholder="Awas! Obat Keras. Bacalah aturan pemakaiannya."
                                className={errors.warning ? 'border-danger' : ''}
                            />
                        </Field>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field
                                label="Batas Pembelian per Transaksi"
                                htmlFor="maxQtyPerOrder"
                                hint={errors.maxQtyPerOrder ?? 'Kosongkan bila tidak dibatasi.'}
                            >
                                <Input
                                    id="maxQtyPerOrder"
                                    type="number"
                                    min="1"
                                    value={data.maxQtyPerOrder}
                                    onChange={(event) => setData('maxQtyPerOrder', event.target.value)}
                                    placeholder="Kosongkan bila tidak dibatasi"
                                    className={errors.maxQtyPerOrder ? 'border-danger' : ''}
                                />
                            </Field>

                            <Field
                                label="Berat (gram)"
                                htmlFor="weightGrams"
                                hint={errors.weightGrams ?? 'Dibutuhkan untuk menghitung ongkir.'}
                            >
                                <Input
                                    id="weightGrams"
                                    type="number"
                                    min="0"
                                    value={data.weightGrams}
                                    onChange={(event) => setData('weightGrams', event.target.value)}
                                    placeholder="150"
                                    className={errors.weightGrams ? 'border-danger' : ''}
                                />
                            </Field>
                        </div>
                    </div>
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
