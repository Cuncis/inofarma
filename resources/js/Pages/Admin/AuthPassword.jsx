import { Link, router } from '@inertiajs/react';
import AdminAuthLayout from '@/Layouts/AdminAuthLayout';
import Button from '@/Components/Admin/Button';
import { Field, Input } from '@/Components/Admin/Form';

export default function AuthPassword() {
    const submit = (event) => {
        event.preventDefault();

        router.visit('/admin/masuk');
    };

    return (
        <AdminAuthLayout
            title="Atur Ulang Kata Sandi"
            heading="Atur Ulang Kata Sandi"
            subheading="Masukkan email terdaftar Anda dan kami akan mengirimkan tautan pemulihan."
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

                <Button type="submit" className="w-full">
                    Kirim Tautan
                </Button>
            </form>

            <p className="mt-5 text-center text-[13px] text-admin-muted dark:text-admin-dark-muted">
                Kembali ke{' '}
                <Link href="/admin/masuk" className="font-semibold text-brand hover:underline">
                    halaman masuk
                </Link>
            </p>
        </AdminAuthLayout>
    );
}
