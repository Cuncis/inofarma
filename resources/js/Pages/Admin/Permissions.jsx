import { Fragment, useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import { permissionGroups, roles } from '@/Components/Admin/data';

/** Seed grants so the matrix opens with a believable state. */
const seed = new Set([
    ...permissionGroups.flatMap((group) =>
        group.abilities.map((ability) => `Super Admin:${group.module}:${ability}`),
    ),
    'Apoteker:Produk:Lihat',
    'Apoteker:Produk:Tambah',
    'Apoteker:Produk:Ubah',
    'Apoteker:Inventaris:Lihat',
    'Apoteker:Inventaris:Sesuaikan Stok',
    'Kasir:Pesanan:Lihat',
    'Kasir:Pesanan:Proses',
    'Kasir:Pelanggan:Lihat',
    'Staf Gudang:Inventaris:Lihat',
    'Staf Gudang:Inventaris:Terima Barang',
]);

export default function Permissions() {
    const [checked, setChecked] = useState(seed);

    const toggle = (key) =>
        setChecked((current) => {
            const next = new Set(current);

            next.has(key) ? next.delete(key) : next.add(key);

            return next;
        });

    return (
        <AdminLayout
            title="Hak Akses"
            heading="Hak Akses"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Hak Akses' }]}
            actions={<Button size="sm">Simpan Perubahan</Button>}
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
                            {permissionGroups.map((group) => (
                                <Fragment key={group.module}>
                                    <tr className="bg-admin-hover dark:bg-admin-dark-hover">
                                        <td
                                            colSpan={roles.length + 1}
                                            className="px-5 py-2 text-[12px] font-bold text-admin-heading dark:text-admin-dark-heading"
                                        >
                                            {group.module}
                                        </td>
                                    </tr>

                                    {group.abilities.map((ability) => (
                                        <tr
                                            key={`${group.module}-${ability}`}
                                            className="border-b border-admin-border last:border-0 dark:border-admin-dark-border"
                                        >
                                            <td className="px-5 py-2.5 text-[13px] text-admin-body dark:text-admin-dark-body">
                                                {ability}
                                            </td>

                                            {roles.map((role) => {
                                                const key = `${role.name}:${group.module}:${ability}`;

                                                return (
                                                    <td key={key} className="px-4 py-2.5 text-center">
                                                        <input
                                                            type="checkbox"
                                                            checked={checked.has(key)}
                                                            onChange={() => toggle(key)}
                                                            aria-label={`${role.name} — ${group.module} ${ability}`}
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
