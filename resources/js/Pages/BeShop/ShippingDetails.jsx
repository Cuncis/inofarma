import { useState } from 'react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Radio from '@/Components/BeShop/Radio';
import { addresses } from '@/Components/BeShop/data';

export default function ShippingDetails() {
    const [selected, setSelected] = useState(addresses[0].title);

    return (
        <MobileLayout
            title="Pengiriman"
            header={<AppBar title="Detail Pengiriman" back="/ui/checkout" />}
            footer={
                <div className="border-t border-line p-3.5">
                    <Button href="/ui/checkout">Kirim ke Sini</Button>
                </div>
            }
        >
            <div className="flex-1 overflow-y-auto p-3.5">
                {addresses.map((address) => (
                    <button
                        key={address.title}
                        type="button"
                        onClick={() => setSelected(address.title)}
                        className={`mb-2 flex w-full items-center gap-3 p-3.5 text-left ${
                            selected === address.title
                                ? 'border border-brand'
                                : 'border border-line'
                        }`}
                    >
                        <div className="flex-1">
                            <div className="mb-0.5 text-sm font-bold">{address.title}</div>
                            <div className="text-xs text-muted">{address.line}</div>
                        </div>

                        <Radio checked={selected === address.title} />
                    </button>
                ))}
            </div>
        </MobileLayout>
    );
}
