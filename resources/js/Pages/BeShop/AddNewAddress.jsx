import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Field from '@/Components/BeShop/Field';

export default function AddNewAddress() {
    return (
        <MobileLayout
            title="Add New Address"
            header={
                <AppBar title="Add a New Address" back="/ui/my-address" tone="white" />
            }
        >
            <div className="flex-1 overflow-y-auto p-4">
                <Field value="Street address" className="mb-2.5" />
                <Field value="City" className="mb-2.5" />
                <Field value="State" className="mb-2.5" />

                <div className="mb-2.5 grid grid-cols-2 gap-2.5">
                    <Field value="ZIP code" placeholder />
                    <Field value="Country" placeholder />
                </div>

                <Field value="Address title (e.g. Home)" className="mb-2.5" />

                <Button>Add Address</Button>
            </div>
        </MobileLayout>
    );
}
