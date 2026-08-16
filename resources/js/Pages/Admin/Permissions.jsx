import { Fragment, useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';

/**
 * @param {{ roles: object[], permissionGroups: Record<string, string[]> }} props
 */
export default function Permissions({ roles, permissionGroups }) {
    const [checked, setChecked] = useState(
        new Set(roles.flatMap((role) => role.grantedPermissions.map((permission) => `${role.name}:${permission}`))),
    );
    const [saving, setSaving] = useState(false);

    const toggle = (key) =>
        setChecked((current) => {
            const next = new Set(current);

            next.has(key) ? next.delete(key) : next.add(key);

            return next;
        });

    const save = () => {
        const grants = {};

        for (const role of roles) {
            grants[role.name] = [];
        }

        for (const key of checked) {
            const [roleName, ...rest] = key.split(':');
            grants[roleName]?.push(rest.join(':'));
        }

        setSaving(true);
        router.post(
            '/admin/hak-akses',
            { grants },
            { preserveScroll: true, onFinish: () => setSaving(false) },
        );
    };

    return (
        <AdminLayout
            title="Hak Akses"
            heading="Hak Akses"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Hak Akses' }]}
            actions={
                <Button size="sm" onClick={save} disabled={saving}>
                    {saving ? 'Menyimpan…' : 'Simpan Perubahan'}
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <div className="overflow-x-auto">
                    <table className="w-full min-w-[720px] border-collapse text-left">
                        <thead>
                            <tr className="border-b border-admin-border dark:border-admin-dark-border">
                                <th className="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-admin-muted dark:text-admin-dark-muted">
                                    Modul &amp; Aksi
                                </th>
                                {roles.map((role) => (
                                    <th
                                        key={role.name}
                                        className="whitespace-nowrap px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-admin-muted dark:text-admin-dark-muted"
                                    >
                                        {role.name}
                                    </th>
                                ))}
                            </tr>
                        </thead>

                        <tbody>
                            {Object.entries(permissionGroups).map(([module, abilities]) => (
                                <Fragment key={module}>
                                    <tr className="bg-admin-hover dark:bg-admin-dark-hover">
                                        <td
                                            colSpan={roles.length + 1}
                                            className="px-5 py-2 text-[12px] font-bold text-admin-heading dark:text-admin-dark-heading"
                                        >
                                            {module}
                                        </td>
                                    </tr>

                                    {abilities.map((ability) => (
                                        <tr
                                            key={`${module}-${ability}`}
                                            className="border-b border-admin-border last:border-0 dark:border-admin-dark-border"
                                        >
                                            <td className="px-5 py-2.5 text-[13px] text-admin-body dark:text-admin-dark-body">
                                                {ability}
                                            </td>

                                            {roles.map((role) => {
                                                const key = `${role.name}:${module}:${ability}`;

                                                return (
                                                    <td key={key} className="px-4 py-2.5 text-center">
                                                        <input
                                                            type="checkbox"
                                                            checked={checked.has(key)}
                                                            onChange={() => toggle(key)}
                                                            aria-label={`${role.name} — ${module} ${ability}`}
                                                            className="h-4 w-4 rounded border-admin-border text-brand focus:ring-brand dark:border-admin-dark-border"
                                                        />
                                                    </td>
                                                );
                                            })}
                                        </tr>
                                    ))}
                                </Fragment>
                            ))}
                        </tbody>
                    </table>
                </div>
            </Card>
        </AdminLayout>
    );
}
