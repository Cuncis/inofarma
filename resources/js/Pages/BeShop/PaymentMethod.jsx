import { useState } from 'react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Icon from '@/Components/BeShop/Icon';
import Radio from '@/Components/BeShop/Radio';
import { cards } from '@/Components/BeShop/data';

const otherMethods = ['Stripe', 'PayPal', 'Apple Pay', 'Google Pay'];

export default function PaymentMethod() {
    const [selected, setSelected] = useState(cards[0]);

    return (
        <MobileLayout
            title="Checkout Payment"
            header={<AppBar title="Payment Method" back="/ui/checkout" />}
            footer={
                <div className="border-t border-line p-3.5">
                    <Button href="/ui/checkout">Use This Method</Button>
                </div>
            }
        >
            <div className="flex-1 overflow-y-auto p-3.5">
                <div className="mb-2 border border-line bg-lilac p-3.5">
                    <div className="mb-2.5 border-b-2 border-ink pb-2 font-display text-[13px]">
                        My cards
                    </div>

                    {cards.map((card) => (
                        <button
                            key={card}
                            type="button"
                            onClick={() => setSelected(card)}
                            className={`mb-2 flex w-full items-center gap-2.5 p-3 text-left ${
                                selected === card ? 'border border-brand' : 'border border-line'
                            }`}
                        >
                            <Icon name="card" size={20} className="text-ink" />
                            <span className="flex-1 text-xs">{card}</span>
                            <Radio checked={selected === card} />
                        </button>
                    ))}
                </div>

                <div className="border border-line bg-lilac p-3.5">
                    <div className="mb-2.5 border-b-2 border-ink pb-2 font-display text-[13px]">
                        Other methods
                    </div>

                    {otherMethods.map((method) => (
                        <button
                            key={method}
                            type="button"
                            onClick={() => setSelected(method)}
                            className={`mb-2 flex w-full items-center gap-2.5 p-3 text-left ${
                                selected === method
                                    ? 'border border-brand'
                                    : 'border border-line'
                            }`}
                        >
                            <span className="flex-1 text-[13px]">{method}</span>
                            <Radio checked={selected === method} />
                        </button>
                    ))}
                </div>
            </div>
        </MobileLayout>
    );
}
