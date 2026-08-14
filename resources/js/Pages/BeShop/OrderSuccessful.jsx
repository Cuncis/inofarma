import MobileLayout from '@/Layouts/MobileLayout';
import Button from '@/Components/BeShop/Button';
import { asset } from '@/Components/BeShop/data';

export default function OrderSuccessful() {
    return (
        <MobileLayout title="Order Successful" background="bg-blush">
            <div className="flex flex-1 flex-col items-center justify-center overflow-y-auto px-6 py-7 text-center">
                <div className="mb-[18px] font-display text-xl tracking-[2px]">BESHOP</div>

                <img
                    src={asset.other('02')}
                    alt=""
                    className="mx-auto mb-4 h-[175px] w-[175px] object-contain"
                />

                <h2 className="mb-2.5 font-display text-[22px]">Your order is accepted!</h2>

                <p className="mb-[22px] text-[13px] leading-relaxed text-muted">
                    Your items has been placed and is
                    <br />
                    on its way to being processed.
                </p>

                <Button href="/ui/track-order" className="mb-2">
                    Track Your Order
                </Button>

                <Button href="/ui/profile" variant="outline">
                    Go to My Profile
                </Button>
            </div>
        </MobileLayout>
    );
}
