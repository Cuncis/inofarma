import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Field from '@/Components/BeShop/Field';

export default function ForgotPassword() {
    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/email-sent');
    };

    return (
        <MobileLayout
            title="Lupa Kata Sandi"
            header={<AppBar title="Lupa Kata Sandi" back="/ui/signin" tone="white" />}
        >
            <form onSubmit={submit} className="flex-1 overflow-y-auto p-5">
                <div className="bg-blush bg-[radial-gradient(circle_at_10%_90%,rgba(254,121,0,.18)_0%,transparent_60%)] p-6">
                    <p className="mb-5 text-[13px] leading-[1.7] text-muted">
                        Masukkan alamat email yang terdaftar pada akun Anda, lalu kami
                        akan mengirimkan tautan untuk mengatur ulang kata sandi.
                    </p>

                    <Field
                        type="email"
                        name="email"
                        placeholder="kirana.wijaya@mail.com"
                        className="mb-2.5"
                    />

                    <Button type="submit">Kirim</Button>
                </div>
            </form>
        </MobileLayout>
    );
}
