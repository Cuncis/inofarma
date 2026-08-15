import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Field from '@/Components/Shop/Field';

export default function NewPassword() {
    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/signin');
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
                    placeholder="Kata sandi baru"
                    className="mb-2.5"
                />
                <Field
                    type="password"
                    name="password_confirmation"
                    placeholder="Ulangi kata sandi baru"
                    className="mb-2.5"
                />

                <Button type="submit">Ubah Kata Sandi</Button>
            </form>
        </MobileLayout>
    );
}
