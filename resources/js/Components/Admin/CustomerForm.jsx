import { useForm } from '@inertiajs/react';
import Button from './Button';
import Card from './Card';
import { Field, Input, Select, Textarea } from './Form';

/**
 * Create/update form for a customer, shared by the add and edit screens.
 *
 * @param {{ customer?: object, statuses: string[], submitLabel: string }} props
 */
export default function CustomerForm({ customer, statuses, submitLabel }) {
    const editing = Boolean(customer);

    const { data, setData, post, put, processing, errors } = useForm({
        name: customer?.name ?? '',
        email: customer?.email ?? '',
        phone: customer?.phone ?? '',
        city: customer?.city ?? '',
        address: customer?.address ?? '',
        status: customer?.status ?? statuses[0],
    });

    const submit = (event) => {
        event.preventDefault();

        const options = { preserveScroll: true };

        if (editing) {
            put(`/admin/pelanggan/${customer.id}`, options);
        } else {
            post('/admin/pelanggan', options);
        }
    };

    return (
        <form onSubmit={submit} className="mx-auto max-w-3xl">
            <Card title="Data Pelanggan">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="Nama Lengkap" htmlFor="name" hint={errors.name}>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(event) => setData('name', event.target.value)}
                            placeholder="Kirana Wijaya"
                            aria-invalid={Boolean(errors.name)}
                            className={errors.name ? 'border-danger' : ''}
                        />
                    </Field>

                    <Field
                        label="Email"
                        htmlFor="email"
                        hint={errors.email ?? 'Dipakai untuk menautkan riwayat pesanan.'}
                    >
                        <Input
                            id="email"
                            type="email"
                            value={data.email}
                            onChange={(event) => setData('email', event.target.value)}
                            placeholder="kirana@mail.com"
                            aria-invalid={Boolean(errors.email)}
                            className={errors.email ? 'border-danger' : ''}
                        />
                    </Field>

                    <Field label="Nomor Telepon" htmlFor="phone" hint={errors.phone}>
                        <Input
                            id="phone"
                            type="tel"
                            value={data.phone}
                            onChange={(event) => setData('phone', event.target.value)}
                            placeholder="+62 812-3456-7890"
                            className={errors.phone ? 'border-danger' : ''}
                        />
                    </Field>

                    <Field label="Kota" htmlFor="city" hint={errors.city}>
                        <Input
                            id="city"
                            value={data.city}
                            onChange={(event) => setData('city', event.target.value)}
                            placeholder="Jakarta Barat"
                            className={errors.city ? 'border-danger' : ''}
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

                    <div className="hidden sm:block" />

                    <Field
                        label="Alamat"
                        htmlFor="address"
                        hint={errors.address}
                        className="sm:col-span-2"
                    >
                        <Textarea
                            id="address"
                            value={data.address}
                            onChange={(event) => setData('address', event.target.value)}
                            placeholder="Jl. Kebon Jeruk Raya No. 27"
                            className={errors.address ? 'border-danger' : ''}
                        />
                    </Field>
                </div>

                <div className="mt-6 flex gap-2 border-t border-admin-border pt-5 dark:border-admin-dark-border">
                    <Button type="submit" disabled={processing}>
                        {processing ? 'Menyimpan…' : submitLabel}
                    </Button>
                    <Button href="/admin/pelanggan" variant="outline">
                        Batal
                    </Button>
                </div>
            </Card>
        </form>
    );
}
