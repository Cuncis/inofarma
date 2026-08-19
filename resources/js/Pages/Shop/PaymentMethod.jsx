import { useState } from 'react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Icon from '@/Components/Shop/Icon';
import Radio from '@/Components/Shop/Radio';
import { cards } from '@/Components/Shop/data';

const otherMethods = ['GoPay', 'OVO', 'DANA', 'Transfer Bank'];

export default function PaymentMethod() {
    const [selected, setSelected] = useState(cards[0]);

    return (
        <MobileLayout
            title="Pembayaran"
            header={<AppBar title="Metode Pembayaran" back="/ui/checkout" tone="brand" />}
            footer={
                <div className="border-t border-line p-3.5">
                    <Button href="/ui/checkout">Gunakan Metode Ini</Button>
                </div>
            }
        >
            <div className="flex-1 overflow-y-auto p-3.5">
                <div className="mb-2 border border-line bg-lilac p-3.5">
                    <div className="mb-2.5 border-b-2 border-ink pb-2 font-display text-[13px]">
                        Kartu saya
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
                        Metode lain
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
