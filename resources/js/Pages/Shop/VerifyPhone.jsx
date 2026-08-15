import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Field from '@/Components/Shop/Field';

export default function VerifyPhone() {
    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/otp-code');
    };

    return (
        <MobileLayout
            title="Verifikasi Nomor HP"
            header={<AppBar title="Verifikasi Nomor HP" back="/ui/signup" tone="white" />}
        >
            <form onSubmit={submit} className="flex-1 overflow-y-auto p-5">
                <div className="bg-blush p-6">
                    <p className="mb-[18px] text-[13px] leading-[1.7] text-muted">
                        Kami telah mengirim SMS berisi kode ke nomor{' '}
                        <strong>+62 812-3456-7890</strong>.
                    </p>

                    <Field
                        type="tel"
                        name="phone"
                        defaultValue="+6281234567890"
                        icon="check"
                        iconClassName="text-success"
                        className="mb-2.5"
                    />

                    <Button type="submit">Konfirmasi</Button>
                </div>
            </form>
        </MobileLayout>
    );
}
