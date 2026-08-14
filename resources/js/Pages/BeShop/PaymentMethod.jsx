import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Icon from '@/Components/BeShop/Icon';
import Radio from '@/Components/BeShop/Radio';
import { cards } from '@/Components/BeShop/data';

const otherMethods = ['Stripe', 'PayPal', 'Apple Pay', 'Google Pay'];

export default function PaymentMethod() {
    return (
        <MobileLayout
            title="Checkout Payment"
            header={<AppBar title="Payment Method" back="/ui/checkout" />}
        >
            <div className="flex-1 overflow-y-auto p-3.5">
                <div className="mb-2 border border-line bg-lilac p-3.5">
                    <div className="mb-2.5 border-b-2 border-ink pb-2 font-display text-[13px]">
                        My cards
                    </div>

                    {cards.map((card, index) => (
                        <div
                            key={card}
                            className={`flex items-center gap-2.5 p-3 ${
                                index === 0
                                    ? 'mb-2 border border-brand'
                                    : 'border border-line'
                            }`}
                        >
                            <Icon name="card" size={20} className="text-ink" />
                            <span className="flex-1 text-xs">{card}</span>
                            <Radio checked={index === 0} />
                        </div>
                    ))}
                </div>

                <div className="border border-line bg-lilac p-3.5">
                    <div className="mb-2.5 border-b-2 border-ink pb-2 font-display text-[13px]">
                        Other methods
                    </div>

                    {otherMethods.map((method) => (
                        <div
                            key={method}
                            className="mb-2 flex items-center gap-2.5 border border-line p-3"
                        >
                            <span className="flex-1 text-[13px]">{method}</span>
                            <Radio />
                        </div>
                    ))}
                </div>
            </div>
        </MobileLayout>
    );
}
