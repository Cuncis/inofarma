import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Icon from '@/Components/BeShop/Icon';
import { swatches } from '@/Components/BeShop/data';

const tags = ['Tops', 'Bottoms', 'Dresses', 'Outerwear', 'Accessories', 'Shoes'];

export default function Filter() {
    return (
        <MobileLayout
            title="Filter"
            header={<AppBar title="Filter" back="/ui/shop" />}
            footer={
                <div className="border-t border-line p-3.5">
                    <Button>Apply Filters</Button>
                </div>
            }
        >
            <div className="flex-1 overflow-y-auto p-4">
                <div className="mb-2.5 font-display text-[15px]">Color</div>

                <div className="mb-[18px] flex flex-wrap gap-2.5">
                    {swatches.map((color, index) => (
                        <div
                            key={color}
                            style={{ background: color }}
                            className={`h-[29px] w-[29px] rounded-full ${
                                index === 0
                                    ? 'outline outline-2 outline-offset-2 outline-brand'
                                    : ''
                            }`}
                        />
                    ))}
                </div>

                <div className="mb-2.5 font-display text-[15px]">Label</div>

                <div className="mb-2.5 flex items-center gap-2.5">
                    <div className="flex h-[17px] w-[17px] items-center justify-center border border-[#cccccc] bg-white text-ink">
                        <Icon name="check" size={12} />
                    </div>
                    <span className="bg-sale px-3 py-1 text-[11px] font-bold text-white">
                        SALE
                    </span>
                </div>

                <div className="mb-[18px] flex items-center gap-2.5">
                    <div className="h-[17px] w-[17px] border border-[#cccccc] bg-white" />
                    <span className="bg-brand px-3 py-1 text-[11px] font-bold text-white">
                        NEW
                    </span>
                </div>

                <div className="mb-2.5 font-display text-[15px]">Tags</div>

                <div className="mb-5 flex flex-wrap gap-2">
                    {tags.map((tag, index) => (
                        <div
                            key={tag}
                            className={`px-3.5 py-[7px] text-xs ${
                                index === 0
                                    ? 'border border-brand text-brand'
                                    : 'border border-line text-muted'
                            }`}
                        >
                            {tag}
                        </div>
                    ))}
                </div>
            </div>
        </MobileLayout>
    );
}
