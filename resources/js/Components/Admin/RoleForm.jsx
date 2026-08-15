import { useState } from 'react';
import { router } from '@inertiajs/react';
import Button from './Button';
import Card from './Card';
import { Field, Input, Textarea } from './Form';
import { permissionGroups } from './data';

/**
 * Role form with the permission matrix, shared by the add and edit screens.
 *
 * Permissions are held as a `"Module:Ability"` set so a checkbox can be toggled
 * without rebuilding the whole grid.
 *
 * @param {{ role?: object, granted?: string[], submitLabel: string }} props
 */
export default function RoleForm({ role, granted = [], submitLabel }) {
    const [checked, setChecked] = useState(new Set(granted));

    const toggle = (key) =>
        setChecked((current) => {
            const next = new Set(current);

            next.has(key) ? next.delete(key) : next.add(key);

            return next;
        });

    const submit = (event) => {
        event.preventDefault();

        router.visit('/admin/peran');
    };

    return (
        <form onSubmit={submit} className="space-y-5">
            <Card title="Informasi Peran">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="Nama Peran" htmlFor="name">
                        <Input id="name" name="name" defaultValue={role?.name} placeholder="Apoteker" />
                    </Field>

                    <Field label="Deskripsi" htmlFor="description">
                        <Input
                            id="description"
                            name="description"
                            defaultValue={role?.description}
                            placeholder="Kelola produk dan stok"
                        />
                    </Field>

                    <Field label="Catatan" htmlFor="notes" className="sm:col-span-2">
                        <Textarea id="notes" name="notes" placeholder="Catatan internal (opsional)..." />
                    </Field>
                </div>
            </Card>

            <Card title="Hak Akses">
                <div className="space-y-5">
                    {permissionGroups.map((group) => (
                        <div key={group.module}>
                            <p className="mb-2.5 text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                {group.module}
                            </p>

                            <div className="flex flex-wrap gap-x-6 gap-y-2.5">
                                {group.abilities.map((ability) => {
                                    const key = `${group.module}:${ability}`;

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
                    <Button type="submit">{submitLabel}</Button>
                    <Button href="/admin/peran" variant="outline">
                        Batal
                    </Button>
                </div>
            </Card>
        </form>
    );
}
