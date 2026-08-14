import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Icon from '@/Components/BeShop/Icon';

const lines = [
    { label: 'Silk Maxi Dress x2', value: '$179.98' },
    { label: 'Leather Jacket x1', value: '$145.00' },
    { label: 'Discount', value: '-$0' },
];

export default function Checkout() {
    return (
        <MobileLayout
            title="Checkout"
            header={<AppBar title="Checkout" back="/ui/cart" />}
        >
            <div className="flex-1 overflow-y-auto p-3.5">
                <div className="mb-2 border border-line bg-lilac p-3.5">
                    <div className="mb-2.5 flex justify-between border-b-2 border-ink pb-2 font-display text-sm">
                        <span>My order</span>
                        <span>$324.98</span>
                    </div>

                    {lines.map((line) => (
                        <div
                            key={line.label}
                            className="mb-[5px] flex justify-between text-xs text-muted"
                        >
                            <span>{line.label}</span>
                            <span>{line.value}</span>
                        </div>
                    ))}

                    <div className="flex justify-between text-xs text-muted">
                        <span>Delivery</span>
                        <span className="text-success">Free</span>
                    </div>
                </div>

                <div className="mb-2 border border-line bg-lilac p-3.5">
                    <div className="mb-2 flex items-center justify-between border-b-2 border-ink pb-2 font-display text-[13px]">
                        <span>Shipping details</span>
                        <Link href="/ui/shipping-details" aria-label="Edit shipping details">
                            <Icon name="edit" size={15} className="text-ink" />
                        </Link>
                    </div>

                    <span className="text-xs text-muted">
                        8000 S Kirkland Ave, Chicago, IL 60652
                    </span>
                </div>

                <div className="mb-2 border border-line bg-lilac p-3.5">
                    <div className="mb-2 flex items-center justify-between border-b-2 border-ink pb-2 font-display text-[13px]">
                        <span>Payment method</span>
                        <Link href="/ui/payment-method" aria-label="Edit payment method">
                            <Icon name="edit" size={15} className="text-ink" />
                        </Link>
                    </div>

                    <span className="text-xs text-muted">**** **** **** 6644</span>
                </div>

                <div className="mb-3.5 min-h-[80px] border border-blush p-3 text-xs text-[#bbbbbb]">
                    Comment (optional)...
                </div>

                <Button href="/ui/order-successful" className="mb-2">
                    Confirm Order ($324.98)
                </Button>
            </div>
        </MobileLayout>
    );
}
