import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminAuthLayout from '@/Layouts/AdminAuthLayout';
import Button from '@/Components/Admin/Button';
import { Field, Input } from '@/Components/Admin/Form';

export default function AuthSignIn() {
    const [remember, setRemember] = useState(true);

    const submit = (event) => {
        event.preventDefault();

        router.visit('/admin');
    };

    return (
        <AdminAuthLayout
            title="Masuk Admin"
            heading="Masuk"
            subheading="Masukkan email dan kata sandi untuk mengakses panel admin."
        >
            <form onSubmit={submit} className="space-y-4">
                <Field label="Email" htmlFor="email">
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        placeholder="admin@inofarma.co.id"
                        autoComplete="email"
                    />
                </Field>

                <Field label="Kata Sandi" htmlFor="password">
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        autoComplete="current-password"
                    />
                </Field>

                <div className="flex items-center justify-between">
                    <label className="flex items-center gap-2 text-[13px] text-admin-body dark:text-admin-dark-body">
                        <input
                            type="checkbox"
                            checked={remember}
                            onChange={() => setRemember((current) => ! current)}
                            className="h-4 w-4 rounded border-admin-border text-brand focus:ring-brand dark:border-admin-dark-border"
                        />
                        Ingat saya
                    </label>

                    <Link href="/admin/auth/atur-ulang-sandi" className="text-[13px] text-brand hover:underline">
                        Lupa sandi?
                    </Link>
                </div>

                <Button type="submit" className="w-full">
                    Masuk
                </Button>
            </form>

            <p className="mt-5 text-center text-[13px] text-admin-muted dark:text-admin-dark-muted">
                Belum punya akun?{' '}
                <Link href="/admin/auth/daftar" className="font-semibold text-brand hover:underline">
                    Daftar
                </Link>
            </p>
        </AdminAuthLayout>
    );
}
