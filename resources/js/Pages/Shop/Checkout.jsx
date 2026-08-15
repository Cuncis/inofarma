import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Icon from '@/Components/Shop/Icon';
import { cartItems, money } from '@/Components/Shop/data';

const orderTotal = cartItems.reduce((total, item) => total + item.amount * item.quantity, 0);

const lines = [
    ...cartItems.map((item) => ({
        label: `${item.name} x${item.quantity}`,
        value: money(item.amount * item.quantity),
    })),
    { label: 'Diskon', value: `-${money(0)}` },
];

export default function Checkout() {
    const [comment, setComment] = useState('');
    const [placing, setPlacing] = useState(false);

    /**
     * Stands in for the order request: a short pause, then the success screen.
     */
    const confirmOrder = () => {
        setPlacing(true);

        window.setTimeout(() => router.visit('/ui/order-successful'), 600);
    };

    return (
        <MobileLayout
            title="Checkout"
            header={<AppBar title="Checkout" back="/ui/cart" />}
        >
            <div className="flex-1 overflow-y-auto p-3.5">
                <div className="mb-2 border border-line bg-lilac p-3.5">
                    <div className="mb-2.5 flex justify-between border-b-2 border-ink pb-2 font-display text-sm">
                        <span>Pesanan saya</span>
                        <span>{money(orderTotal)}</span>
                    </div>

                    {lines.map((line) => (
                        <div
                            key={line.label}
                            className="mb-[5px] flex justify-between text-xs text-muted"
                        >
                            <span>{line.label}</span>
                            <span>{line.value}</span>
                        </div>
                    ))}

                    <div className="flex justify-between text-xs text-muted">
                        <span>Pengiriman</span>
                        <span className="text-success-deep">Gratis</span>
                    </div>
                </div>

                <Link
                    href="/ui/shipping-details"
                    className="mb-2 block border border-line bg-lilac p-3.5"
                >
                    <div className="mb-2 flex items-center justify-between border-b-2 border-ink pb-2 font-display text-[13px]">
                        <span>Detail pengiriman</span>
                        <Icon name="edit" size={15} className="text-ink" />
                    </div>

                    <span className="text-xs text-muted">
                        Jl. Kebon Jeruk Raya No. 27, Jakarta Barat 11530
                    </span>
                </Link>

                <Link
                    href="/ui/payment-method"
                    className="mb-2 block border border-line bg-lilac p-3.5"
                >
                    <div className="mb-2 flex items-center justify-between border-b-2 border-ink pb-2 font-display text-[13px]">
                        <span>Metode pembayaran</span>
                        <Icon name="edit" size={15} className="text-ink" />
                    </div>

                    <span className="text-xs text-muted">**** **** **** 6644</span>
                </Link>

                <textarea
                    value={comment}
                    onChange={(event) => setComment(event.target.value)}
                    placeholder="Catatan (opsional)..."
                    rows={3}
                    className="mb-3.5 w-full resize-none border border-blush p-3 text-xs text-muted placeholder:text-[#bbbbbb] focus:outline-none focus:ring-0"
                />

                <Button onClick={confirmOrder} disabled={placing} className="mb-2">
                    {placing ? 'Memproses pesanan…' : `Konfirmasi Pesanan (${money(orderTotal)})`}
                </Button>
            </div>
        </MobileLayout>
    );
}
