import { useForm } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Field from '@/Components/Shop/Field';

/**
 * Reached from the reset-password email link, `?token=...&email=...` — an
 * Inertia GET visit keeps the query string in the browser URL even though
 * this route renders through the generic `$beShopScreens` loop, so it's read
 * client-side rather than passed as a prop.
 */
export default function NewPassword() {
    const params = new URLSearchParams(window.location.search);

    const { data, setData, post, processing, errors } = useForm({
        token: params.get('token') ?? '',
        email: params.get('email') ?? '',
        password: '',
        password_confirmation: '',
    });

    const submit = (event) => {
        event.preventDefault();

        post('/ui/atur-ulang-sandi');
    };

    return (
        <MobileLayout
            title="Kata Sandi Baru"
            header={<AppBar title="Atur Ulang Sandi" back="/ui/email-sent" tone="white" />}
        >
            <form onSubmit={submit} className="flex-1 overflow-y-auto p-[18px]">
                <p className="mb-4 text-[13px] leading-relaxed text-muted">
                    Masukkan kata sandi baru lalu konfirmasi.
                </p>

                <Field
                    type="password"
                    name="password"
                    value={data.password}
                    onChange={(event) => setData('password', event.target.value)}
                    placeholder="Kata sandi baru"
                    error={errors.password}
                    className="mb-2.5"
                />
                <Field
                    type="password"
                    name="password_confirmation"
                    value={data.password_confirmation}
                    onChange={(event) => setData('password_confirmation', event.target.value)}
                    placeholder="Ulangi kata sandi baru"
                    className="mb-2.5"
                />

                <Button type="submit" disabled={processing}>
                    {processing ? 'Memproses…' : 'Ubah Kata Sandi'}
                </Button>
            </form>
        </MobileLayout>
    );
}
