import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Icon from '@/Components/BeShop/Icon';

const pastOrders = [
    {
        number: '#205321',
        status: 'Delivered',
        statusClass: 'bg-[#e6f4ee] text-success',
        date: '10 Aug 2025',
        total: '$89.99',
    },
    {
        number: '#205100',
        status: 'Canceled',
        statusClass: 'bg-[#fce8ef] text-brand',
        date: '02 Aug 2025',
        total: '$42.00',
    },
];

export default function OrderHistory() {
    return (
        <MobileLayout
            title="Order History"
            header={<AppBar title="Order History" back="/ui/profile" />}
        >
            <div className="flex-1 overflow-y-auto p-3.5">
                <div className="mb-2 border border-line">
                    <Link href="/ui/track-order" className="block bg-lilac p-3.5">
                        <div className="mb-[5px] flex justify-between">
                            <span className="font-display text-sm">#205479</span>
                            <span className="bg-[#FFF4E5] px-2.5 py-[3px] text-[11px] font-bold text-warning">
                                On Its Way
                            </span>
                        </div>

                        <div className="flex justify-between">
                            <span className="text-[11px] text-[#aaaaaa]">14 Aug 2025</span>
                            <span className="text-xs font-bold">$324.98</span>
                        </div>
                    </Link>

                    <div className="border-t border-line bg-lilac p-3.5">
                        <div className="mb-1.5 flex justify-between text-xs text-muted">
                            <span>Silk Maxi Dress</span>
                            <span>2 x $89.99</span>
                        </div>

                        <div className="mb-2.5 flex justify-between text-xs text-muted">
                            <span>Leather Jacket</span>
                            <span>1 x $145.00</span>
                        </div>

                        <Link
                            href="/ui/leave-a-review"
                            className="flex items-center justify-between"
                        >
                            <Icon name="message" size={17} className="text-brand" />
                            <span className="text-xs text-brand">Leave a review</span>
                        </Link>
                    </div>
                </div>

                {pastOrders.map((order) => (
                    <Link
                        key={order.number}
                        href="/ui/track-order"
                        className="mb-2 block border border-line p-3.5"
                    >
                        <div className="mb-[5px] flex justify-between">
                            <span className="font-display text-sm">{order.number}</span>
                            <span
                                className={`px-2.5 py-[3px] text-[11px] font-bold ${order.statusClass}`}
                            >
                                {order.status}
                            </span>
                        </div>

                        <div className="flex justify-between">
                            <span className="text-[11px] text-[#aaaaaa]">{order.date}</span>
                            <span className="text-xs font-bold">{order.total}</span>
                        </div>
                    </Link>
                ))}
            </div>
        </MobileLayout>
    );
}
