import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Fab from '@/Components/BeShop/Fab';
import Icon from '@/Components/BeShop/Icon';
import { promocodes } from '@/Components/BeShop/data';

export default function MyPromocodes() {
    return (
        <MobileLayout
            title="My Promocodes"
            header={<AppBar title="My Promocodes" back="/ui/profile" />}
        >
            <div className="flex-1 overflow-y-auto px-3.5 pb-[90px] pt-3.5">
                <div className="mb-3.5 flex border-b border-line">
                    <div className="flex-1 border-b-2 border-ink p-[11px] text-center text-[13px] font-bold">
                        Current
                    </div>
                    <div className="flex-1 p-[11px] text-center text-[13px] font-bold text-[#aaaaaa]">
                        Used
                    </div>
                </div>

                {promocodes.map((promo) => (
                    <div key={promo.code} className="mb-2.5 border border-line p-3.5">
                        <div className="mb-1.5 flex items-center gap-2">
                            <Icon name="tag" size={18} className="text-ink" />
                            <span className="flex-1 font-display text-sm">{promo.name}</span>
                            <span className={`font-bold ${promo.tone}`}>
                                {promo.discount}
                            </span>
                        </div>

                        <div className="mb-2 text-[11px] text-[#aaaaaa]">{promo.expires}</div>

                        <div className="flex items-center justify-between border border-dashed border-[#dddddd] bg-lilac px-3 py-2 text-xs font-bold tracking-[1px]">
                            {promo.code}
                            <Icon name="copy" size={15} className="text-[#aaaaaa]" />
                        </div>
                    </div>
                ))}
            </div>

            <Fab href="/ui/promocodes-empty" label="Add a promocode" />
        </MobileLayout>
    );
}
