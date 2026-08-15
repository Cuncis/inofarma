import { useState } from 'react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Fab from '@/Components/BeShop/Fab';
import Icon from '@/Components/BeShop/Icon';
import { addresses } from '@/Components/BeShop/data';

export default function MyAddress() {
    const [saved, setSaved] = useState(addresses.slice(0, 2));

    return (
        <MobileLayout
            title="Alamat Saya"
            header={<AppBar title="Alamat Saya" back="/ui/profile" />}
        >
            <div className="flex-1 overflow-y-auto px-3.5 pb-[90px] pt-3.5">
                {saved.map((address) => (
                    <div
                        key={address.title}
                        className="mb-2 flex items-start gap-2.5 border border-line p-3.5"
                    >
                        <Icon name="pin" size={19} className="text-ink" />

                        <div className="flex-1">
                            <div className="mb-0.5 text-sm font-bold">{address.title}</div>
                            <div className="text-xs text-muted">{address.line}</div>
                        </div>

                        <button
                            type="button"
                            onClick={() =>
                                setSaved((current) =>
                                    current.filter(
                                        (existing) => existing.title !== address.title,
                                    ),
                                )
                            }
                            aria-label={`Hapus alamat ${address.title}`}
                        >
                            <Icon name="trash" size={17} className="text-[#cccccc]" />
                        </button>
                    </div>
                ))}

                {saved.length === 0 ? (
                    <p className="mt-10 text-center text-[13px] text-muted">
                        Belum ada alamat tersimpan. Tambahkan alamat untuk melanjutkan.
                    </p>
                ) : null}
            </div>

            <Fab href="/ui/add-new-address" label="Tambah alamat baru" />
        </MobileLayout>
    );
}
