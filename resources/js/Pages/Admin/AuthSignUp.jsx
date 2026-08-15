import { Link, router } from '@inertiajs/react';
import AdminAuthLayout from '@/Layouts/AdminAuthLayout';
import Button from '@/Components/Admin/Button';
import { Field, Input } from '@/Components/Admin/Form';

export default function AuthSignUp() {
    const submit = (event) => {
        event.preventDefault();

        router.visit('/admin');
    };

    return (
        <AdminAuthLayout
            title="Daftar Admin"
            heading="Buat Akun"
            subheading="Lengkapi data di bawah ini untuk membuat akun admin baru."
        >
            <form onSubmit={submit} className="space-y-4">
                <Field label="Nama Lengkap" htmlFor="name">
                    <Input id="name" name="name" placeholder="Kirana Wijaya" autoComplete="name" />
                </Field>

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
                    <Input id="password" type="password" name="password" placeholder="••••••••" />
                </Field>

                <Field label="Ulangi Kata Sandi" htmlFor="password_confirmation">
                    <Input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        placeholder="••••••••"
                    />
                </Field>

                <label className="flex items-start gap-2 text-[13px] text-admin-body dark:text-admin-dark-body">
                    <input
                        type="checkbox"
                        defaultChecked
                        className="mt-0.5 h-4 w-4 rounded border-admin-border text-brand focus:ring-brand dark:border-admin-dark-border"
                    />
                    <span>
                        Saya menyetujui{' '}
                        <Link href="/admin/kebijakan-privasi" className="text-brand hover:underline">
                            syarat &amp; kebijakan privasi
                        </Link>
                        .
                    </span>
                </label>

                <Button type="submit" className="w-full">
                    Daftar
                </Button>
            </form>

            <p className="mt-5 text-center text-[13px] text-admin-muted dark:text-admin-dark-muted">
                Sudah punya akun?{' '}
                <Link href="/admin/auth/masuk" className="font-semibold text-brand hover:underline">
                    Masuk
                </Link>
            </p>
        </AdminAuthLayout>
    );
}
