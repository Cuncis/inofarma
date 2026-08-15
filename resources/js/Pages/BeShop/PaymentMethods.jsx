import { useState } from 'react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Fab from '@/Components/BeShop/Fab';
import Icon from '@/Components/BeShop/Icon';
import { cards } from '@/Components/BeShop/data';

export default function PaymentMethods() {
    const [saved, setSaved] = useState(cards);

    return (
        <MobileLayout
            title="Metode Pembayaran"
            header={<AppBar title="Metode Pembayaran" back="/ui/profile" />}
        >
            <div className="flex-1 overflow-y-auto px-3.5 pb-[90px] pt-3.5">
                {saved.map((card) => (
                    <div
                        key={card}
                        className="mb-2 flex items-center gap-2.5 border border-line p-3.5"
                    >
                        <Icon name="card" size={20} className="text-ink" />
                        <span className="flex-1 text-xs">{card}</span>

                        <button
                            type="button"
                            onClick={() =>
                                setSaved((current) =>
                                    current.filter((existing) => existing !== card),
                                )
                            }
                            aria-label={`Hapus kartu berakhiran ${card.slice(-4)}`}
                        >
                            <Icon name="trash" size={17} className="text-[#cccccc]" />
                        </button>
                    </div>
                ))}

                {saved.length === 0 ? (
                    <p className="mt-10 text-center text-[13px] text-muted">
                        Belum ada kartu tersimpan. Tambahkan kartu untuk melanjutkan.
                    </p>
                ) : null}
            </div>

            <Fab href="/ui/add-new-card" label="Tambah kartu baru" />
        </MobileLayout>
    );
}
