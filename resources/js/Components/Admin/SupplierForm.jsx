import { useForm } from '@inertiajs/react';
import Button from './Button';
import Card from './Card';
import { Field, Input, Select, Textarea } from './Form';

/**
 * Create/update form for a supplier, shared by the add and edit screens.
 *
 * @param {{ supplier?: object, statuses: string[], submitLabel: string }} props
 */
export default function SupplierForm({ supplier, statuses, submitLabel }) {
    const editing = Boolean(supplier);

    const { data, setData, post, put, processing, errors } = useForm({
        name: supplier?.name ?? '',
        owner: supplier?.owner ?? '',
        email: supplier?.email ?? '',
        phone: supplier?.phone ?? '',
        license: supplier?.license ?? '',
        city: supplier?.city ?? '',
        address: supplier?.address ?? '',
        status: supplier?.status ?? statuses[0],
    });

    const submit = (event) => {
        event.preventDefault();

        const options = { preserveScroll: true };

        if (editing) {
            put(`/admin/pemasok/${supplier.id}`, options);
        } else {
            post('/admin/pemasok', options);
        }
    };

    const field = (name, label, extra = {}) => (
        <Field label={label} htmlFor={name} hint={errors[name] ?? extra.hint}>
            <Input
                id={name}
                type={extra.type ?? 'text'}
                value={data[name]}
                onChange={(event) => setData(name, event.target.value)}
                placeholder={extra.placeholder}
                aria-invalid={Boolean(errors[name])}
                className={errors[name] ? 'border-danger' : ''}
            />
        </Field>
    );

    return (
        <form onSubmit={submit} className="mx-auto max-w-3xl">
            <Card title="Data Pemasok">
                <div className="grid gap-4 sm:grid-cols-2">
                    {field('name', 'Nama Toko', { placeholder: 'Apotek Sehat Bersama' })}
                    {field('owner', 'Nama Pemilik', { placeholder: 'Kirana Wijaya' })}
                    {field('email', 'Email', { type: 'email', placeholder: 'apotek@mail.com' })}
                    {field('phone', 'Nomor Telepon', { type: 'tel', placeholder: '+62 21 5551 0001' })}
                    {field('license', 'Nomor Izin Apotek', {
                        placeholder: 'SIA/2025/00123',
                        hint: 'Harus unik untuk setiap pemasok.',
                    })}
                    {field('city', 'Kota', { placeholder: 'Jakarta Selatan' })}

                    <Field label="Status" htmlFor="status" hint={errors.status}>
                        <Select
                            id="status"
                            value={data.status}
                            onChange={(event) => setData('status', event.target.value)}
                            options={statuses}
                        />
                    </Field>

                    <div className="hidden sm:block" />

                    <Field
                        label="Alamat Toko"
                        htmlFor="address"
                        hint={errors.address}
                        className="sm:col-span-2"
                    >
                        <Textarea
                            id="address"
                            value={data.address}
                            onChange={(event) => setData('address', event.target.value)}
                            placeholder="Jl. Jend. Sudirman Kav. 52-53"
                            className={errors.address ? 'border-danger' : ''}
                        />
                    </Field>
                </div>

                {editing && supplier.products > 0 ? (
                    <p className="mt-4 rounded-lg bg-admin-hover px-3 py-2.5 text-xs leading-relaxed text-admin-body dark:bg-admin-dark-hover dark:text-admin-dark-body">
                        Mengubah nama toko akan otomatis memperbarui{' '}
                        <strong>{supplier.products} produk</strong> yang dipasoknya.
                    </p>
                ) : null}

                <div className="mt-6 flex gap-2 border-t border-admin-border pt-5 dark:border-admin-dark-border">
                    <Button type="submit" disabled={processing}>
                        {processing ? 'Menyimpan…' : submitLabel}
                    </Button>
                    <Button href="/admin/pemasok" variant="outline">
                        Batal
                    </Button>
                </div>
            </Card>
        </form>
    );
}
