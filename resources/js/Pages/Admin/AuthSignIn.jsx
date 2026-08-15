import { Link, useForm } from '@inertiajs/react';
import AdminAuthLayout from '@/Layouts/AdminAuthLayout';
import Button from '@/Components/Admin/Button';
import { Field, Input } from '@/Components/Admin/Form';

export default function AuthSignIn() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: true,
    });

    const submit = (event) => {
        event.preventDefault();

        post('/admin/masuk');
    };

    return (
        <AdminAuthLayout
            title="Masuk Admin"
            heading="Masuk"
            subheading="Masukkan email dan kata sandi untuk mengakses panel admin."
        >
            <form onSubmit={submit} className="space-y-4">
                <Field label="Email" htmlFor="email" hint={errors.email}>
                    <Input
                        id="email"
                        type="email"
                        value={data.email}
                        onChange={(event) => setData('email', event.target.value)}
                        placeholder="admin@inofarma.co.id"
                        autoComplete="email"
                        aria-invalid={Boolean(errors.email)}
                        className={errors.email ? 'border-danger' : ''}
                    />
                </Field>

                <Field label="Kata Sandi" htmlFor="password" hint={errors.password}>
                    <Input
                        id="password"
                        type="password"
                        value={data.password}
                        onChange={(event) => setData('password', event.target.value)}
                        placeholder="••••••••"
                        autoComplete="current-password"
                        aria-invalid={Boolean(errors.password)}
                        className={errors.password ? 'border-danger' : ''}
                    />
                </Field>

                <div className="flex items-center justify-between">
                    <label className="flex items-center gap-2 text-[13px] text-admin-body dark:text-admin-dark-body">
                        <input
                            type="checkbox"
                            checked={data.remember}
                            onChange={() => setData('remember', ! data.remember)}
                            className="h-4 w-4 rounded border-admin-border text-brand focus:ring-brand dark:border-admin-dark-border"
                        />
                        Ingat saya
                    </label>

                    <Link href="/admin/lupa-sandi" className="text-[13px] text-brand hover:underline">
                        Lupa sandi?
                    </Link>
                </div>

                <Button type="submit" disabled={processing} className="w-full">
                    {processing ? 'Memproses…' : 'Masuk'}
                </Button>
            </form>

            <p className="mt-5 rounded-lg bg-admin-hover px-3 py-2.5 text-center text-xs leading-relaxed text-admin-body dark:bg-admin-dark-hover dark:text-admin-dark-body">
                Belum terhubung ke basis data — email dan kata sandi apa pun bisa masuk.
            </p>
        </AdminAuthLayout>
    );
}
