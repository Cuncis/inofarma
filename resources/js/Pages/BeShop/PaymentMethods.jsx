import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Fab from '@/Components/BeShop/Fab';
import Icon from '@/Components/BeShop/Icon';
import { cards } from '@/Components/BeShop/data';

export default function PaymentMethods() {
    return (
        <MobileLayout
            title="Payment Method"
            header={<AppBar title="Payment Method" back="/ui/profile" />}
        >
            <div className="flex-1 overflow-y-auto px-3.5 pb-[90px] pt-3.5">
                {cards.map((card) => (
                    <div
                        key={card}
                        className="mb-2 flex items-center gap-2.5 border border-line p-3.5"
                    >
                        <Icon name="card" size={20} className="text-ink" />
                        <span className="flex-1 text-xs">{card}</span>
                        <Icon name="trash" size={17} className="text-[#cccccc]" />
                    </div>
                ))}
            </div>

            <Fab href="/ui/add-new-card" label="Add a new card" />
        </MobileLayout>
    );
}
