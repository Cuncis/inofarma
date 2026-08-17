import { useForm } from '@inertiajs/react';
import Button from './Button';
import Card from './Card';
import { Field, Input, Select } from './Form';

/**
 * Create/update form for an attribute, shared by the add and edit screens.
 *
 * `values` is edited as a comma-separated line and split/joined at the form
 * boundary — the database stores it as a JSON array (`Attribute::$casts`),
 * but a text field is a far simpler way to type "250mg, 500mg, 1000mg" than
 * a repeatable input group for a handful of short words.
 *
 * @param {{ attribute?: object, types: string[], submitLabel: string }} props
 */
export default function AttributeForm({ attribute, types, submitLabel }) {
    const editing = Boolean(attribute);

    const { data, setData, transform, post, put, processing, errors } = useForm({
        name: attribute?.name ?? '',
        type: attribute?.type ?? types[0],
        values: (attribute?.values ?? []).join(', '),
    });

    const isPilihan = data.type === 'Pilihan';

    transform((current) => ({
        ...current,
        values: isPilihan
            ? current.values.split(',').map((value) => value.trim()).filter(Boolean)
            : [],
    }));

    const submit = (event) => {
        event.preventDefault();

        const options = { preserveScroll: true };

        if (editing) {
            put(`/admin/atribut/${attribute.id}`, options);
        } else {
            post('/admin/atribut', options);
        }
    };

    return (
        <form onSubmit={submit} className="mx-auto max-w-3xl">
            <Card title="Informasi Atribut">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="Nama Atribut" htmlFor="name" hint={errors.name}>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(event) => setData('name', event.target.value)}
                            placeholder="Bentuk Sediaan"
                            className={errors.name ? 'border-danger' : ''}
                        />
                    </Field>

                    <Field label="Tipe" htmlFor="type" hint={errors.type}>
                        <Select
                            id="type"
                            value={data.type}
                            onChange={(event) => setData('type', event.target.value)}
                            options={types}
                        />
                    </Field>

                    {isPilihan ? (
                        <Field
                            label="Nilai"
                            htmlFor="values"
                            hint={errors.values ?? 'Pisahkan setiap nilai dengan koma.'}
                            className="sm:col-span-2"
                        >
                            <Input
                                id="values"
                                value={data.values}
                                onChange={(event) => setData('values', event.target.value)}
                                placeholder="Tablet, Kapsul, Sirup, Salep"
                                className={errors.values ? 'border-danger' : ''}
                            />
                        </Field>
                    ) : null}
                </div>

                <div className="mt-6 flex gap-2 border-t border-admin-border pt-5 dark:border-admin-dark-border">
                    <Button type="submit" disabled={processing}>
                        {processing ? 'Menyimpan…' : submitLabel}
                    </Button>
                    <Button href="/admin/atribut" variant="outline">
                        Batal
                    </Button>
                </div>
            </Card>
        </form>
    );
}
