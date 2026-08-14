import MobileLayout from '@/Layouts/MobileLayout';
import Button from '@/Components/BeShop/Button';
import { asset } from '@/Components/BeShop/data';

export default function AccountCreated() {
    return (
        <MobileLayout title="Account Created" background="bg-blush">
            <div className="flex flex-1 flex-col items-center justify-center overflow-y-auto px-6 py-7 text-center">
                <div className="mb-[18px] font-display text-xl tracking-[2px]">BESHOP</div>

                <img
                    src={asset.other('01')}
                    alt=""
                    className="mx-auto mb-4 h-[180px] w-[180px] object-contain"
                />

                <h2 className="mb-2.5 font-display text-[22px]">Account Created!</h2>

                <p className="mb-[22px] text-[13px] leading-relaxed text-muted">
                    Your account had been created
                    <br />
                    successfully.
                </p>

                <Button href="/">Shop Now</Button>
            </div>
        </MobileLayout>
    );
}
