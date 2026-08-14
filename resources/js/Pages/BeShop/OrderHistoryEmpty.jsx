import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import { asset } from '@/Components/BeShop/data';

export default function OrderHistoryEmpty() {
    return (
        <MobileLayout
            title="Order History Empty"
            header={<AppBar title="Order History" back="/ui/profile" tone="ink" />}
        >
            <div className="flex flex-1 flex-col items-center justify-center overflow-y-auto px-6 py-7 text-center">
                <img
                    src={asset.other('03')}
                    alt=""
                    className="mx-auto mb-4 h-[180px] w-[180px] object-contain"
                />

                <h2 className="mb-2.5 font-display text-[21px] leading-tight">
                    You do not have
                    <br />
                    orders yet!
                </h2>

                <p className="mb-[22px] text-[13px] leading-relaxed text-muted">
                    Your order history is empty.
                    <br />
                    Start shopping!
                </p>

                <Button href="/ui/shop">Shop Now</Button>
            </div>
        </MobileLayout>
    );
}
