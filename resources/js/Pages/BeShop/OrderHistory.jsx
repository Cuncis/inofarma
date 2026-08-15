import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Icon from '@/Components/BeShop/Icon';
import { money } from '@/Components/BeShop/data';

const pastOrders = [
    {
        number: '#205321',
        status: 'Diterima',
        statusClass: 'bg-success text-ink',
        date: '10 Agu 2025',
        total: money(1350000),
    },
    {
        number: '#205100',
        status: 'Dibatalkan',
        statusClass: 'bg-line text-muted',
        date: '02 Agu 2025',
        total: money(630000),
    },
];

export default function OrderHistory() {
    return (
        <MobileLayout
            title="Riwayat Pesanan"
            header={<AppBar title="Riwayat Pesanan" back="/ui/profile" />}
        >
            <div className="flex-1 overflow-y-auto p-3.5">
                <div className="mb-2 border border-line">
                    <Link href="/ui/track-order" className="block bg-lilac p-3.5">
                        <div className="mb-[5px] flex justify-between">
                            <span className="font-display text-sm">#205479</span>
                            <span className="bg-warning px-2.5 py-[3px] text-[11px] font-bold text-ink">
                                Dalam Perjalanan
                            </span>
                        </div>

                        <div className="flex justify-between">
                            <span className="text-[11px] text-[#aaaaaa]">14 Agu 2025</span>
                            <span className="text-xs font-bold">{money(4875000)}</span>
                        </div>
                    </Link>

                    <div className="border-t border-line bg-lilac p-3.5">
                        <div className="mb-1.5 flex justify-between text-xs text-muted">
                            <span>Dress Maxi Sutra</span>
                            <span>2 x {money(1350000)}</span>
                        </div>

                        <div className="mb-2.5 flex justify-between text-xs text-muted">
                            <span>Jaket Kulit</span>
                            <span>1 x {money(2175000)}</span>
                        </div>

                        <Link
                            href="/ui/leave-a-review"
                            className="flex items-center justify-between"
                        >
                            <Icon name="message" size={17} className="text-brand" />
                            <span className="text-xs text-brand">Beri ulasan</span>
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
