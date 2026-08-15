import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Field from '@/Components/Shop/Field';

export default function AddNewAddress() {
    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/my-address');
    };

    return (
        <MobileLayout
            title="Tambah Alamat Baru"
            header={
                <AppBar title="Tambah Alamat Baru" back="/ui/my-address" tone="white" />
            }
        >
            <form onSubmit={submit} className="flex-1 overflow-y-auto p-4">
                <Field name="street" placeholder="Alamat lengkap (jalan, no. rumah)" className="mb-2.5" />
                <Field name="city" placeholder="Kota / Kabupaten" className="mb-2.5" />
                <Field name="state" placeholder="Provinsi" className="mb-2.5" />

                <div className="mb-2.5 grid grid-cols-2 gap-2.5">
                    <Field name="zip" placeholder="Kode pos" inputMode="numeric" />
                    <Field name="country" placeholder="Negara" />
                </div>

                <Field
                    name="title"
                    placeholder="Label alamat (mis. Rumah)"
                    className="mb-2.5"
                />

                <Button type="submit">Tambah Alamat</Button>
            </form>
        </MobileLayout>
    );
}
