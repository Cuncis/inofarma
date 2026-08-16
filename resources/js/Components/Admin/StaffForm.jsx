import { useForm } from '@inertiajs/react';
import Button from './Button';
import Card from './Card';
import { Field, Input, Select, Switch } from './Form';

/**
 * @param {{
 *   staff?: object,
 *   branches: { id: number, name: string }[],
 *   roles: string[],
 *   submitLabel: string,
 * }} props
 */
export default function StaffForm({ staff, branches, roles, submitLabel }) {
    const { data, setData, post, put, processing, errors } = useForm({
        name: staff?.name ?? '',
        email: staff?.email ?? '',
        phone: staff?.phone ?? '',
        password: '',
        password_confirmation: '',
        branchId: staff?.branchId ?? '',
        isActive: staff?.isActive ?? true,
        roles: staff?.roles ?? [],
    });

    const toggleRole = (role) =>
        setData(
            'roles',
            data.roles.includes(role) ? data.roles.filter((item) => item !== role) : [...data.roles, role],
        );

    const submit = (event) => {
        event.preventDefault();

        if (staff) {
            put(`/admin/staf/${staff.id}`);
        } else {
            post('/admin/staf');
        }
    };

    return (
        <form onSubmit={submit} className="grid gap-5 lg:grid-cols-3">
            <div className="space-y-5 lg:col-span-2">
                <Card title="Identitas">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label="Nama" htmlFor="name" hint={errors.name}>
                            <Input id="name" value={data.name} onChange={(event) => setData('name', event.target.value)} />
                        </Field>

                        <Field label="Email" htmlFor="email" hint={errors.email}>
                            <Input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(event) => setData('email', event.target.value)}
                            />
                        </Field>

                        <Field label="Nomor HP" htmlFor="phone" hint={errors.phone}>
                            <Input id="phone" value={data.phone} onChange={(event) => setData('phone', event.target.value)} />
                        </Field>

                        <Field label="Cabang" htmlFor="branchId" hint={errors.branchId}>
                            <Select
                                id="branchId"
                                value={data.branchId}
                                onChange={(event) => setData('branchId', event.target.value)}
                                options={[
                                    { value: '', label: 'Pusat (semua cabang)' },
                                    ...branches.map((branch) => ({ value: branch.id, label: branch.name })),
                                ]}
                            />
                        </Field>
                    </div>
                </Card>

                <Card title="Kata Sandi">
                    <p className="mb-3 text-xs text-admin-muted dark:text-admin-dark-muted">
                        {staff ? 'Kosongkan jika tidak ingin mengubah kata sandi.' : 'Wajib diisi untuk akun baru.'}
                    </p>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label="Kata Sandi" htmlFor="password" hint={errors.password}>
                            <Input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(event) => setData('password', event.target.value)}
                            />
                        </Field>

                        <Field label="Ulangi Kata Sandi" htmlFor="password_confirmation">
                            <Input
                                id="password_confirmation"
                                type="password"
                                value={data.password_confirmation}
                                onChange={(event) => setData('password_confirmation', event.target.value)}
                            />
                        </Field>
                    </div>
                </Card>

                <Card title="Peran">
                    <div className="flex flex-wrap gap-x-6 gap-y-2.5">
                        {roles.map((role) => (
                            <label
                                key={role}
                                className="flex items-center gap-2 text-[13px] text-admin-body dark:text-admin-dark-body"
                            >
                                <input
                                    type="checkbox"
                                    checked={data.roles.includes(role)}
                                    onChange={() => toggleRole(role)}
                                    className="h-4 w-4 rounded border-admin-border text-brand focus:ring-brand dark:border-admin-dark-border"
                                />
                                {role}
                            </label>
                        ))}
                    </div>
                </Card>
            </div>

            <div className="space-y-5">
                <Card title="Status">
                    <Switch
                        checked={data.isActive}
                        onChange={() => setData('isActive', ! data.isActive)}
                        label="Akun aktif"
                    />
                </Card>

                <Button type="submit" disabled={processing} className="w-full">
                    {processing ? 'Menyimpan…' : submitLabel}
                </Button>
                <Button href="/admin/staf" variant="outline" className="w-full">
                    Batal
                </Button>
            </div>
        </form>
    );
}
