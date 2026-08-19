import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Field from '@/Components/Shop/Field';
import useShopUser from '@/Components/Shop/useShopUser';
import { asset } from '@/Components/Shop/data';

export default function EditProfile() {
    const user = useShopUser();

    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/profile');
    };

    return (
        <MobileLayout
            title="Ubah Profil"
            header={<AppBar title="Ubah Profil" back="/ui/profile" tone="brand" />}
        >
            <form
                onSubmit={submit}
                className="flex flex-1 flex-col items-center overflow-y-auto bg-blush px-[22px] py-[18px]"
            >
                <div className="mx-auto mb-[18px] mt-3.5 h-[108px] w-[108px] overflow-hidden rounded-full border-[5px] border-brand">
                    <img
                        src={asset.user('01')}
                        alt={user.name}
                        className="h-full w-full object-cover"
                    />
                </div>

                <div className="w-full">
                    <Field
                        type="email"
                        name="email"
                        defaultValue={user.email}
                        className="mb-2.5"
                    />
                    <Field
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        className="mb-2.5"
                    />
                    <Field
                        type="tel"
                        name="phone"
                        defaultValue="+62 812-3456-7890"
                        className="mb-2.5"
                    />
                    <Field
                        name="address"
                        placeholder="Masukkan alamat Anda"
                        className="mb-2.5"
                    />

                    <Button type="submit">Simpan Perubahan</Button>
                </div>
            </form>
        </MobileLayout>
    );
}
