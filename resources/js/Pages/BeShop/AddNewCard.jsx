import { useState } from 'react';
import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Field from '@/Components/BeShop/Field';

export default function AddNewCard() {
    const [card, setCard] = useState({ number: '', holder: '', expiry: '' });

    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/payment-methods');
    };

    return (
        <MobileLayout
            title="Tambah Kartu Baru"
            header={
                <AppBar title="Tambah Kartu Baru" back="/ui/payment-methods" tone="white" />
            }
        >
            <form onSubmit={submit} className="flex-1 overflow-y-auto p-4">
                <div className="mb-5 rounded bg-gradient-to-br from-[#252525] to-[#555555] p-[22px] text-white">
                    <div className="mb-4 text-[11px] opacity-60">Kartu Kredit</div>

                    <div className="mb-4 font-mono text-[15px] tracking-[2px]">
                        {card.number || '1234 5678 9012 3456'}
                    </div>

                    <div className="flex justify-between text-xs opacity-80">
                        <span>{card.holder.toUpperCase() || 'KIRANA WIJAYA'}</span>
                        <span>{card.expiry || '12/26'}</span>
                    </div>
                </div>

                <Field
                    name="number"
                    placeholder="Nomor kartu"
                    inputMode="numeric"
                    onChange={(event) =>
                        setCard((current) => ({ ...current, number: event.target.value }))
                    }
                    className="mb-2.5"
                />

                <Field
                    name="holder"
                    placeholder="Nama pemilik kartu"
                    onChange={(event) =>
                        setCard((current) => ({ ...current, holder: event.target.value }))
                    }
                    className="mb-2.5"
                />

                <div className="mb-2.5 grid grid-cols-2 gap-2.5">
                    <Field
                        name="expiry"
                        placeholder="BB/TT"
                        onChange={(event) =>
                            setCard((current) => ({
                                ...current,
                                expiry: event.target.value,
                            }))
                        }
                    />
                    <Field name="cvv" placeholder="CVV" inputMode="numeric" />
                </div>

                <Button type="submit">Tambah Kartu</Button>
            </form>
        </MobileLayout>
    );
}
