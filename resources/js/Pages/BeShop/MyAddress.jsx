import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Fab from '@/Components/BeShop/Fab';
import Icon from '@/Components/BeShop/Icon';
import { addresses } from '@/Components/BeShop/data';

export default function MyAddress() {
    return (
        <MobileLayout
            title="My Address"
            header={<AppBar title="My Address" back="/ui/profile" />}
        >
            <div className="flex-1 overflow-y-auto px-3.5 pb-[90px] pt-3.5">
                {addresses.slice(0, 2).map((address) => (
                    <div
                        key={address.title}
                        className="mb-2 flex items-start gap-2.5 border border-line p-3.5"
                    >
                        <Icon name="pin" size={19} className="text-ink" />

                        <div className="flex-1">
                            <div className="mb-0.5 text-sm font-bold">{address.title}</div>
                            <div className="text-xs text-muted">{address.line}</div>
                        </div>

                        <Icon name="trash" size={17} className="text-[#cccccc]" />
                    </div>
                ))}
            </div>

            <Fab href="/ui/add-new-address" label="Add a new address" />
        </MobileLayout>
    );
}
