import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Fab from '@/Components/Shop/Fab';
import FlashBanner from '@/Components/Shop/FlashBanner';
import Icon from '@/Components/Shop/Icon';

/**
 * @param {{ addresses: object[] }} props
 */
export default function MyAddress({ addresses }) {
    const remove = (address) => {
        router.delete(`/ui/alamat/${address.id}`, { preserveScroll: true });
    };

    const makeDefault = (address) => {
        router.post(`/ui/alamat/${address.id}/utama`, {}, { preserveScroll: true });
    };

    return (
        <MobileLayout
            title="Alamat Saya"
            header={<AppBar title="Alamat Saya" back="/ui/profile" tone="brand" />}
        >
            <FlashBanner />

            <div className="flex-1 overflow-y-auto px-3.5 pb-[90px] pt-3.5">
                {addresses.map((address) => (
                    <div
                        key={address.id}
                        className={`mb-2 flex items-start gap-2.5 rounded-lg border bg-white p-3.5 ${
                            address.isDefault ? 'border-brand' : 'border-line'
                        }`}
                    >
                        <Icon name="pin" size={19} className="text-ink" />

                        <div className="flex-1">
                            <div className="mb-0.5 flex items-center gap-1.5 text-sm font-bold">
                                {address.label}
                                {address.isDefault ? (
                                    <span className="rounded-sm bg-brand px-1.5 py-0.5 text-[9px] font-bold uppercase text-white">
                                        Utama
                                    </span>
                                ) : null}
                            </div>
                            <div className="text-xs text-muted">{address.fullAddress}</div>

                            {! address.isDefault ? (
                                <button
                                    type="button"
                                    onClick={() => makeDefault(address)}
                                    className="mt-1 text-[11px] text-brand"
                                >
                                    Jadikan utama
                                </button>
                            ) : null}
                        </div>

                        <button
                            type="button"
                            onClick={() => remove(address)}
                            aria-label={`Hapus alamat ${address.label}`}
                        >
                            <Icon name="trash" size={17} className="text-[#cccccc]" />
                        </button>
                    </div>
                ))}

                {addresses.length === 0 ? (
                    <p className="mt-10 text-center text-[13px] text-muted">
                        Belum ada alamat tersimpan. Tambahkan alamat untuk melanjutkan.
                    </p>
                ) : null}
            </div>

            <Fab href="/ui/add-new-address" label="Tambah alamat baru" />
        </MobileLayout>
    );
}
