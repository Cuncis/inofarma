import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Radio from '@/Components/BeShop/Radio';
import { addresses } from '@/Components/BeShop/data';

export default function ShippingDetails() {
    return (
        <MobileLayout
            title="Checkout Shipping"
            header={<AppBar title="Shipping Details" back="/ui/checkout" />}
        >
            <div className="flex-1 overflow-y-auto p-3.5">
                {addresses.map((address, index) => (
                    <div
                        key={address.title}
                        className={`mb-2 flex items-center gap-3 p-3.5 ${
                            index === 0 ? 'border border-brand' : 'border border-line'
                        }`}
                    >
                        <div className="flex-1">
                            <div className="mb-0.5 text-sm font-bold">{address.title}</div>
                            <div className="text-xs text-muted">{address.line}</div>
                        </div>

                        <Radio checked={index === 0} />
                    </div>
                ))}
            </div>
        </MobileLayout>
    );
}
