import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import IconLink from '@/Components/BeShop/IconLink';
import TabBar from '@/Components/BeShop/TabBar';
import { cartItems, money } from '@/Components/BeShop/data';

export default function Cart() {
    const [items, setItems] = useState(cartItems);
    const [promo, setPromo] = useState('');
    const [applied, setApplied] = useState(false);

    /**
     * Adjust a line quantity. Dropping to zero removes the line, and emptying the
     * cart entirely sends the shopper to the empty-cart screen.
     *
     * @param {string} name
     * @param {number} delta
     */
    const changeQuantity = (name, delta) => {
        const next = items
            .map((item) =>
                item.name === name
                    ? { ...item, quantity: item.quantity + delta }
                    : item,
            )
            .filter((item) => item.quantity > 0);

        if (next.length === 0) {
            router.visit('/ui/cart-empty');

            return;
        }

        setItems(next);
    };

    const subtotal = items.reduce((total, item) => total + item.amount * item.quantity, 0);
    const discount = applied ? subtotal * 0.15 : 0;

    return (
        <MobileLayout
            title="Cart / Order"
            header={
                <AppBar
                    title="Order"
                    actions={
                        <>
                            <IconLink
                                name="bag"
                                href="/ui/order-history"
                                label="Order history"
                            />
                            <IconLink name="user" href="/ui/profile" label="Profile" />
                        </>
                    }
                />
            }
            footer={<TabBar active="order" />}
        >
            <div className="flex-1 overflow-y-auto px-3.5 pt-3.5">
                {items.map((item) => (
                    <div
                        key={item.name}
                        className="mb-2 flex h-[88px] gap-2.5 border border-line p-2.5"
                    >
                        <Link
                            href="/ui/product-detail"
                            className="relative w-[68px] shrink-0"
                        >
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
                        </Link>

                        <Link
                            href="/ui/product-detail"
                            className="flex flex-1 flex-col justify-center"
                        >
                            <div className="mb-1 text-[13px] font-semibold text-ink">
                                {item.name}
                            </div>
                            <div
                                className={`text-xs font-bold ${
                                    item.onSale ? 'text-brand' : 'text-muted'
                                }`}
                            >
                                {money(item.amount)}
                            </div>
                        </Link>

                        <div className="flex min-w-[24px] flex-col items-center justify-between py-1">
                            <button
                                type="button"
                                onClick={() => changeQuantity(item.name, 1)}
                                aria-label={`Increase ${item.name} quantity`}
                                className="flex h-[22px] w-[22px] items-center justify-center border border-line text-sm leading-none"
                            >
                                +
                            </button>

                            <span className="text-xs">{item.quantity}</span>

                            <button
                                type="button"
                                onClick={() => changeQuantity(item.name, -1)}
                                aria-label={`Decrease ${item.name} quantity`}
                                className="flex h-[22px] w-[22px] items-center justify-center border border-line text-sm leading-none"
                            >
                                −
                            </button>
                        </div>
                    </div>
                ))}

                <div className="mb-[18px] grid grid-cols-[1.5fr_1fr] gap-2">
                    <input
                        value={promo}
                        onChange={(event) => setPromo(event.target.value)}
                        placeholder="Enter promo code"
                        className="h-[50px] border border-blush px-3.5 text-xs text-muted placeholder:text-[#bbbbbb] focus:outline-none focus:ring-0"
                    />

                    <button
                        type="button"
                        onClick={() => setApplied(promo.trim().length > 0)}
                        className={`flex h-[50px] items-center justify-center border text-xs font-bold uppercase ${
                            applied
                                ? 'border-brand bg-brand text-white'
                                : 'border-line bg-lilac'
                        }`}
                    >
                        {applied ? 'Applied' : 'Apply'}
                    </button>
                </div>

                <div className="mb-3.5 border border-line bg-lilac p-3.5">
                    <div className="mb-1.5 flex justify-between text-[13px]">
                        <span>Subtotal</span>
                        <span className="font-bold">{money(subtotal)}</span>
                    </div>

                    {applied ? (
                        <div className="mb-1.5 flex justify-between text-[13px]">
                            <span>Discount</span>
                            <span className="text-brand">-{money(discount)}</span>
                        </div>
                    ) : null}

                    <div className="mb-1.5 flex justify-between border-b-2 border-ink pb-1.5 text-[13px]">
                        <span>Delivery</span>
                        <span className="text-success">Free</span>
                    </div>

                    <div className="flex justify-between text-[13px]">
                        <span className="font-bold">Total</span>
                        <span className="font-bold">{money(subtotal - discount)}</span>
                    </div>
                </div>

                <Button href="/ui/checkout" className="mb-2">
                    Proceed to Checkout
                </Button>
            </div>
        </MobileLayout>
    );
}
