import { Link, router } from '@inertiajs/react';
import AdminAuthLayout from '@/Layouts/AdminAuthLayout';
import Button from '@/Components/Admin/Button';
import { Field, Input } from '@/Components/Admin/Form';
import { img } from '@/Components/Admin/data';

export default function AuthLockScreen() {
    const submit = (event) => {
        event.preventDefault();

        router.visit('/admin');
    };

    return (
        <AdminAuthLayout title="Kunci Layar">
            <div className="mb-6 text-center">
                <img
                    src={img.user(1)}
                    alt="Kirana Wijaya"
                    className="mx-auto mb-3 h-20 w-20 rounded-full object-cover"
                />
                <h1 className="text-lg font-semibold text-admin-heading dark:text-admin-dark-heading">
                    Kirana Wijaya
                </h1>
                <p className="mt-1 text-[13px] text-admin-muted dark:text-admin-dark-muted">
                    Masukkan kata sandi untuk membuka kembali sesi Anda.
                </p>
            </div>

            <form onSubmit={submit} className="space-y-4">
                <Field label="Kata Sandi" htmlFor="password">
                    <Input id="password" type="password" name="password" placeholder="••••••••" />
                </Field>

                <Button type="submit" className="w-full">
                    Buka Kunci
                </Button>
            </form>

            <p className="mt-5 text-center text-[13px] text-admin-muted dark:text-admin-dark-muted">
                Bukan Anda?{' '}
                <Link href="/admin/auth/masuk" className="font-semibold text-brand hover:underline">
                    Masuk akun lain
                </Link>
            </p>
        </AdminAuthLayout>
    );
}
