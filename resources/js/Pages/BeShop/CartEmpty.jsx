import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Icon from '@/Components/BeShop/Icon';
import TabBar from '@/Components/BeShop/TabBar';
import { asset } from '@/Components/BeShop/data';

export default function CartEmpty() {
    return (
        <MobileLayout
            title="Cart Empty"
            header={<AppBar tone="white" actions={<Icon name="user" size={19} />} />}
            footer={<TabBar active="order" />}
        >
            <div className="flex flex-1 flex-col items-center justify-center overflow-y-auto px-6 py-7 text-center">
                <img
                    src={asset.other('03')}
                    alt=""
                    className="mx-auto mb-4 h-[190px] w-[190px] object-contain"
                />

                <h2 className="mb-2.5 font-display text-[22px]">Your cart is empty!</h2>

                <p className="mb-[22px] text-[13px] leading-relaxed text-muted">
                    Looks like you have not made
                    <br />
                    your order yet.
                </p>

                <Button href="/ui/shop">Shop Now</Button>
            </div>
        </MobileLayout>
    );
}
