import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import { asset } from '@/Components/BeShop/data';

export default function OrderFailed() {
    return (
        <MobileLayout
            title="Order Failed"
            header={<AppBar back="/ui/checkout" tone="white" />}
        >
            <div className="flex flex-1 flex-col items-center justify-center overflow-y-auto px-6 py-7 text-center">
                <img
                    src={asset.other('05')}
                    alt=""
                    className="mx-auto mb-4 h-[175px] w-[175px] object-contain"
                />

                <h2 className="mb-2.5 font-display text-[22px]">
                    Sorry! Your order
                    <br />
                    has failed!
                </h2>

                <p className="mb-[22px] text-[13px] leading-relaxed text-muted">
                    Something went wrong. Please try
                    <br />
                    again to continue your order.
                </p>

                <Button href="/ui/checkout" className="mb-2">
                    Try Again
                </Button>

                <Button href="/ui/profile" variant="outline">
                    Go to My Profile
                </Button>
            </div>
        </MobileLayout>
    );
}
