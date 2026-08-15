import { useForm } from '@inertiajs/react';
import Button from './Button';
import Card from './Card';
import { Field, Input, Select, Textarea } from './Form';

/**
 * Create/update form for a category, shared by the add and edit screens.
 *
 * @param {{
 *   category?: object,
 *   statuses: string[],
 *   submitLabel: string,
 * }} props
 */
export default function CategoryForm({ category, statuses, submitLabel }) {
    const editing = Boolean(category);

    const { data, setData, post, put, processing, errors } = useForm({
        name: category?.name ?? '',
        slug: category?.slug ?? '',
        status: category?.status ?? statuses[0],
        description: category?.description ?? '',
    });

    const submit = (event) => {
        event.preventDefault();

        const options = { preserveScroll: true };

        if (editing) {
            put(`/admin/kategori/${category.id}`, options);
        } else {
            post('/admin/kategori', options);
        }
    };

    return (
        <form onSubmit={submit} className="mx-auto max-w-3xl">
            <Card title="Informasi Kategori">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="Nama Kategori" htmlFor="name" hint={errors.name}>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(event) => setData('name', event.target.value)}
                            placeholder="Obat Bebas"
                            aria-invalid={Boolean(errors.name)}
                            className={errors.name ? 'border-danger' : ''}
                        />
                    </Field>

                    <Field
                        label="Slug"
                        htmlFor="slug"
                        hint={errors.slug ?? 'Kosongkan untuk dibuat otomatis dari nama.'}
                    >
                        <Input
                            id="slug"
                            value={data.slug}
                            onChange={(event) => setData('slug', event.target.value)}
                            placeholder="obat-bebas"
                            className={errors.slug ? 'border-danger' : ''}
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
                        label="Deskripsi"
                        htmlFor="description"
                        hint={errors.description}
                        className="sm:col-span-2"
                    >
                        <Textarea
                            id="description"
                            value={data.description}
                            onChange={(event) => setData('description', event.target.value)}
                            placeholder="Jelaskan kategori ini..."
                            className={errors.description ? 'border-danger' : ''}
                        />
                    </Field>
                </div>

                {editing ? (
                    <p className="mt-4 rounded-lg bg-admin-hover px-3 py-2.5 text-xs leading-relaxed text-admin-body dark:bg-admin-dark-hover dark:text-admin-dark-body">
                        Mengubah nama kategori akan otomatis memperbarui{' '}
                        <strong>{category.products ?? 0} produk</strong> yang memakainya.
                    </p>
                ) : null}

                <div className="mt-6 flex gap-2 border-t border-admin-border pt-5 dark:border-admin-dark-border">
                    <Button type="submit" disabled={processing}>
                        {processing ? 'Menyimpan…' : submitLabel}
                    </Button>
                    <Button href="/admin/kategori" variant="outline">
                        Batal
                    </Button>
                </div>
            </Card>
        </form>
    );
}
