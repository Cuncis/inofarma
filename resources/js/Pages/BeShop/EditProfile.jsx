import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Field from '@/Components/BeShop/Field';
import useBeShopUser from '@/Components/BeShop/useBeShopUser';
import { asset } from '@/Components/BeShop/data';

export default function EditProfile() {
    const user = useBeShopUser();

    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/profile');
    };

    return (
        <MobileLayout
            title="Edit Profile"
            header={<AppBar title="Edit Profile" back="/ui/profile" tone="white" />}
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
                        defaultValue="+17 123456789"
                        className="mb-2.5"
                    />
                    <Field
                        name="address"
                        placeholder="Enter your address"
                        className="mb-2.5"
                    />

                    <Button type="submit">Save Changes</Button>
                </div>
            </form>
        </MobileLayout>
    );
}
