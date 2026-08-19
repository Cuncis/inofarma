import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import FlashBanner from '@/Components/Shop/FlashBanner';
import { money } from '@/Components/Shop/data';

const toneFor = (status) => {
    if (status === 'Selesai') {
        return 'bg-success text-ink';
    }

    if (status === 'Dibatalkan' || status === 'Kedaluwarsa') {
        return 'bg-line text-muted';
    }

    return 'bg-warning text-ink';
};

/**
 * @param {{ orders: object[] }} props
 */
export default function OrderHistory({ orders }) {
    return (
        <MobileLayout
            title="Riwayat Pesanan"
            header={<AppBar title="Riwayat Pesanan" back="/ui/profile" tone="brand" />}
        >
            <FlashBanner />

            <div className="flex-1 overflow-y-auto p-3.5">
                {orders.map((order) => (
                    <Link
                        key={order.number}
                        href={`/ui/track-order/${order.number}`}
                        className="mb-2 block rounded-lg border border-line bg-white p-3.5"
                    >
                        <div className="mb-[5px] flex justify-between">
                            <span className="font-display text-sm">{order.number}</span>
                            <span
                                className={`px-2.5 py-[3px] text-[11px] font-bold ${toneFor(order.status)}`}
                            >
                                {order.status}
                            </span>
                        </div>

                        <div className="mb-[5px] flex justify-between text-[11px] text-muted">
                            <span>{order.fulfilment}</span>
                            <span>{order.branchName}</span>
                        </div>

                        <div className="flex justify-between">
                            <span className="text-[11px] text-[#aaaaaa]">{order.date}</span>
                            <span className="text-xs font-bold">{money(order.total)}</span>
                        </div>
                    </Link>
                ))}

                {orders.length === 0 ? (
                    <p className="mt-10 text-center text-[13px] text-muted">
                        Anda belum pernah memesan.
                    </p>
                ) : null}
            </div>
        </MobileLayout>
    );
}
