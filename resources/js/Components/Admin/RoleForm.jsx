import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import Button from './Button';
import Card from './Card';
import { Field, Input, Textarea } from './Form';

/**
 * Role form with the permission matrix, shared by the add and edit screens.
 *
 * Permissions are held as a `"Module:Ability"` set, matching the permission
 * names `RolePermissionSeeder` seeds and `Role::syncPermissions()` expects.
 *
 * @param {{
 *   role?: { name: string, description?: string, grantedPermissions: string[] },
 *   permissionGroups: Record<string, string[]>,
 *   submitLabel: string,
 * }} props
 */
export default function RoleForm({ role, permissionGroups, submitLabel }) {
    const [checked, setChecked] = useState(new Set(role?.grantedPermissions ?? []));

    const { data, setData, post, put, processing, errors } = useForm({
        name: role?.name ?? '',
        description: role?.description ?? '',
        permissions: role?.grantedPermissions ?? [],
    });

    const toggle = (key) =>
        setChecked((current) => {
            const next = new Set(current);

            next.has(key) ? next.delete(key) : next.add(key);
            setData('permissions', Array.from(next));

            return next;
        });

    const submit = (event) => {
        event.preventDefault();

        if (role) {
            put(`/admin/peran/${role.name}`);
        } else {
            post('/admin/peran');
        }
    };

    return (
        <form onSubmit={submit} className="space-y-5">
            <Card title="Informasi Peran">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="Nama Peran" htmlFor="name" hint={errors.name}>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(event) => setData('name', event.target.value)}
                            placeholder="Apoteker"
                        />
                    </Field>

                    <Field label="Deskripsi" htmlFor="description" hint={errors.description}>
                        <Input
                            id="description"
                            value={data.description}
                            onChange={(event) => setData('description', event.target.value)}
                            placeholder="Kelola produk dan stok"
                        />
                    </Field>
                </div>
            </Card>

            <Card title="Hak Akses">
                <div className="space-y-5">
                    {Object.entries(permissionGroups).map(([module, abilities]) => (
                        <div key={module}>
                            <p className="mb-2.5 text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                {module}
                            </p>

                            <div className="flex flex-wrap gap-x-6 gap-y-2.5">
                                {abilities.map((ability) => {
                                    const key = `${module}:${ability}`;

                                    return (
                                        <label
                                            key={key}
                                            className="flex items-center gap-2 text-[13px] text-admin-body dark:text-admin-dark-body"
                                        >
                                            <input
                                                type="checkbox"
                                                checked={checked.has(key)}
                                                onChange={() => toggle(key)}
                                                className="h-4 w-4 rounded border-admin-border text-brand focus:ring-brand dark:border-admin-dark-border"
                                            />
                                            {ability}
                                        </label>
                                    );
                                })}
                            </div>
                        </div>
                    ))}
                </div>

                <div className="mt-6 flex gap-2 border-t border-admin-border pt-5 dark:border-admin-dark-border">
                    <Button type="submit" disabled={processing}>
                        {processing ? 'Menyimpan…' : submitLabel}
                    </Button>
                    <Button href="/admin/peran" variant="outline">
                        Batal
                    </Button>
                </div>
            </Card>
        </form>
    );
}
