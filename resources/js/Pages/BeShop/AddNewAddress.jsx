import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Field from '@/Components/BeShop/Field';

export default function AddNewAddress() {
    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/my-address');
    };

    return (
        <MobileLayout
            title="Add New Address"
            header={
                <AppBar title="Add a New Address" back="/ui/my-address" tone="white" />
            }
        >
            <form onSubmit={submit} className="flex-1 overflow-y-auto p-4">
                <Field name="street" placeholder="Street address" className="mb-2.5" />
                <Field name="city" placeholder="City" className="mb-2.5" />
                <Field name="state" placeholder="State" className="mb-2.5" />

                <div className="mb-2.5 grid grid-cols-2 gap-2.5">
                    <Field name="zip" placeholder="ZIP code" inputMode="numeric" />
                    <Field name="country" placeholder="Country" />
                </div>

                <Field
                    name="title"
                    placeholder="Address title (e.g. Home)"
                    className="mb-2.5"
                />

                <Button type="submit">Add Address</Button>
            </form>
        </MobileLayout>
    );
}
