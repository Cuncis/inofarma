import { useForm, usePage } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Field from '@/Components/Shop/Field';

export default function VerifyPhone() {
    const { shopUser } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({ phone: shopUser?.phone ?? '' });

    const submit = (event) => {
        event.preventDefault();

        post('/ui/verify-phone');
    };

    return (
        <MobileLayout
            title="Verifikasi Nomor HP"
            header={<AppBar title="Verifikasi Nomor HP" back="/ui/signup" tone="white" />}
        >
            <form onSubmit={submit} className="flex-1 overflow-y-auto p-5">
                <div className="bg-blush p-6">
                    <p className="mb-[18px] text-[13px] leading-[1.7] text-muted">
                        Kami akan mengirim kode verifikasi ke nomor ini.
                    </p>

                    <Field
                        type="tel"
                        name="phone"
                        value={data.phone}
                        onChange={(event) => setData('phone', event.target.value)}
                        error={errors.phone}
                        className="mb-2.5"
                    />

                    <Button type="submit" disabled={processing}>
                        {processing ? 'Mengirim…' : 'Konfirmasi'}
                    </Button>
                </div>
            </form>
        </MobileLayout>
    );
}
