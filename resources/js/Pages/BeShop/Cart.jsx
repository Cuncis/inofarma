import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Icon from '@/Components/BeShop/Icon';
import TabBar from '@/Components/BeShop/TabBar';
import { cartItems } from '@/Components/BeShop/data';

export default function Cart() {
    return (
        <MobileLayout
            title="Cart / Order"
            header={
                <AppBar
                    title="Order"
                    actions={
                        <>
                            <Icon name="bag" size={19} />
                            <Icon name="user" size={19} />
                        </>
                    }
                />
            }
            footer={<TabBar active="order" />}
        >
            <div className="flex-1 overflow-y-auto px-3.5 pt-3.5">
                {cartItems.map((item) => (
                    <div
                        key={item.name}
                        className="mb-2 flex h-[88px] gap-2.5 border border-line p-2.5"
                    >
                        <div className="relative w-[68px] shrink-0">
                            <img
                                src={item.image}
                                alt={item.name}
                                className="h-full w-[68px] object-cover"
                            />

                            {item.onSale ? (
                                <div className="absolute right-0 top-0 bg-sale px-1.5 py-0.5 text-[8px] font-bold text-white">
                                    SALE
                                </div>
                            ) : null}
                        </div>

                        <div className="flex flex-1 flex-col justify-center">
                            <div className="mb-1 text-[13px] font-semibold text-ink">
                                {item.name}
                            </div>
                            <div
                                className={`text-xs font-bold ${
                                    item.onSale ? 'text-brand' : 'text-muted'
                                }`}
                            >
                                {item.price}
                            </div>
                        </div>

                        <div className="flex min-w-[24px] flex-col items-center justify-between py-1">
                            <div className="flex h-[22px] w-[22px] items-center justify-center border border-line text-sm leading-none">
                                +
                            </div>
                            <span className="text-xs">{item.quantity}</span>
                            <div className="flex h-[22px] w-[22px] items-center justify-center border border-line text-sm leading-none">
                                −
                            </div>
                        </div>
                    </div>
                ))}

                <div className="mb-[18px] grid grid-cols-[1.5fr_1fr] gap-2">
                    <div className="flex h-[50px] items-center border border-blush px-3.5 text-xs text-[#bbbbbb]">
                        Enter promo code
                    </div>
                    <div className="flex h-[50px] items-center justify-center border border-line bg-lilac text-xs font-bold uppercase">
                        Apply
                    </div>
                </div>

                <div className="mb-3.5 border border-line bg-lilac p-3.5">
                    <div className="mb-1.5 flex justify-between text-[13px]">
                        <span>Subtotal</span>
                        <span className="font-bold">$324.98</span>
                    </div>

                    <div className="mb-1.5 flex justify-between border-b-2 border-ink pb-1.5 text-[13px]">
                        <span>Delivery</span>
                        <span className="text-success">Free</span>
                    </div>

                    <div className="flex justify-between text-[13px]">
                        <span className="font-bold">Total</span>
                        <span className="font-bold">$324.98</span>
                    </div>
                </div>

                <Button href="/ui/checkout" className="mb-2">
                    Proceed to Checkout
                </Button>
            </div>
        </MobileLayout>
    );
}
