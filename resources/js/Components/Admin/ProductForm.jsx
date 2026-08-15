import { useState } from 'react';
import { router } from '@inertiajs/react';
import Button from './Button';
import Card from './Card';
import { DropZone, Field, Input, Select, Switch, Textarea } from './Form';
import { categories } from './data';

/**
 * Create/update form for a product, shared by the add and edit screens.
 *
 * @param {{ product?: object, submitLabel: string }} props
 */
export default function ProductForm({ product, submitLabel }) {
    const [published, setPublished] = useState(product?.status !== 'Nonaktif');

    const submit = (event) => {
        event.preventDefault();

        router.visit('/admin/produk');
    };

    return (
        <form onSubmit={submit} className="grid gap-5 lg:grid-cols-3">
            <div className="space-y-5 lg:col-span-2">
                <Card title="Informasi Produk">
                    <div className="space-y-4">
                        <Field label="Nama Produk" htmlFor="name">
                            <Input
                                id="name"
                                name="name"
                                defaultValue={product?.name}
                                placeholder="Paracetamol 500mg"
                            />
                        </Field>

                        <Field
                            label="Deskripsi"
                            htmlFor="description"
                            hint="Jelaskan komposisi, indikasi, dan aturan pakai."
                        >
                            <Textarea
                                id="description"
                                name="description"
                                placeholder="Tuliskan deskripsi produk..."
                            />
                        </Field>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label="Kategori" htmlFor="category">
                                <Select
                                    id="category"
                                    name="category"
                                    defaultValue={product?.category}
                                    options={categories.map((item) => item.name)}
                                />
                            </Field>

                            <Field label="Satuan" htmlFor="unit">
                                <Select
                                    id="unit"
                                    name="unit"
                                    options={['Strip', 'Botol', 'Box', 'Tablet', 'Pcs']}
                                />
                            </Field>
                        </div>
                    </div>
                </Card>

                <Card title="Harga & Stok">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label="Harga Jual (Rp)" htmlFor="price">
                            <Input
                                id="price"
                                name="price"
                                type="number"
                                inputMode="numeric"
                                defaultValue={product?.price}
                                placeholder="12500"
                            />
                        </Field>

                        <Field label="Harga Modal (Rp)" htmlFor="cost">
                            <Input id="cost" name="cost" type="number" placeholder="9000" />
                        </Field>

                        <Field label="Stok" htmlFor="stock">
                            <Input
                                id="stock"
                                name="stock"
                                type="number"
                                defaultValue={product?.stock}
                                placeholder="100"
                            />
                        </Field>

                        <Field label="SKU" htmlFor="sku">
                            <Input id="sku" name="sku" defaultValue={product?.id} placeholder="PRD-001" />
                        </Field>
                    </div>
                </Card>

                <Card title="Gambar Produk">
                    <DropZone />
                </Card>
            </div>

            <div className="space-y-5">
                <Card title="Publikasi">
                    <div className="space-y-4">
                        <Switch
                            checked={published}
                            onChange={() => setPublished((current) => ! current)}
                            label={published ? 'Tampil di etalase' : 'Disembunyikan'}
                        />

                        <Field label="Status Stok" htmlFor="status">
                            <Select
                                id="status"
                                name="status"
                                defaultValue={product?.status}
                                options={['Aktif', 'Stok Menipis', 'Habis', 'Nonaktif']}
                            />
                        </Field>

                        <Field label="Butuh Resep" htmlFor="prescription">
                            <Select
                                id="prescription"
                                name="prescription"
                                options={['Tidak', 'Ya']}
                            />
                        </Field>
                    </div>
                </Card>

                <Card title="Pengiriman">
                    <div className="space-y-4">
                        <Field label="Berat (gram)" htmlFor="weight">
                            <Input id="weight" name="weight" type="number" placeholder="150" />
                        </Field>

                        <Field label="Gudang" htmlFor="warehouse">
                            <Select
                                id="warehouse"
                                name="warehouse"
                                options={['Gudang Jakarta', 'Gudang Bandung', 'Gudang Surabaya']}
                            />
                        </Field>
                    </div>
                </Card>

                <div className="flex gap-2">
                    <Button type="submit" className="flex-1">
                        {submitLabel}
                    </Button>
                    <Button href="/admin/produk" variant="outline">
                        Batal
                    </Button>
                </div>
            </div>
        </form>
    );
}
