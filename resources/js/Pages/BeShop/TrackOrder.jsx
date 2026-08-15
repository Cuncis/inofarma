import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Icon from '@/Components/BeShop/Icon';
import { asset } from '@/Components/BeShop/data';

const steps = [
    { label: 'Pesanan Dibuat', at: '14 Agu 2025, 09.00 WIB', state: 'done' },
    { label: 'Sedang Diproses', at: '14 Agu 2025, 11.30 WIB', state: 'current' },
    { label: 'Dalam Perjalanan', at: 'Menunggu', state: 'pending' },
    { label: 'Diterima', at: 'Menunggu', state: 'pending' },
];

export default function TrackOrder() {
    return (
        <MobileLayout
            title="Lacak Pesanan"
            background="bg-blush"
            header={<AppBar title="Lacak Pesanan" back="/ui/order-history" />}
        >
            <div className="flex-1 overflow-y-auto bg-blush p-[18px]">
                <img
                    src={asset.other('08')}
                    alt=""
                    className="mx-auto mb-3.5 h-40 w-40 object-contain"
                />

                <div className="mb-[3px] text-center font-display text-[17px]">
                    Pesanan Anda:
                </div>
                <div className="mb-[22px] text-center text-[13px] text-muted">#205479</div>

                <div className="px-2">
                    {steps.map((step, index) => (
                        <div key={step.label} className="flex gap-[18px]">
                            <div className="flex flex-col items-center">
                                {step.state === 'done' ? (
                                    <div className="flex h-[22px] w-[22px] items-center justify-center rounded-full border-2 border-brand bg-brand text-white">
                                        <Icon name="check" size={12} />
                                    </div>
                                ) : step.state === 'current' ? (
                                    <div className="flex h-[22px] w-[22px] items-center justify-center rounded-full border-2 border-brand">
                                        <div className="h-2.5 w-2.5 rounded-full bg-brand" />
                                    </div>
                                ) : (
                                    <div className="h-[22px] w-[22px] rounded-full border-2 border-[#cccccc]" />
                                )}

                                {index < steps.length - 1 ? (
                                    <div
                                        className={`my-[3px] h-8 w-0.5 ${
                                            step.state === 'done' ? 'bg-brand' : 'bg-[#dddddd]'
                                        }`}
                                    />
                                ) : null}
                            </div>

                            <div className="pt-0.5">
                                <div
                                    className={`mb-0.5 text-[13px] font-semibold ${
                                        step.state === 'pending' ? 'text-[#bbbbbb]' : 'text-ink'
                                    }`}
                                >
                                    {step.label}
                                </div>
                                <div className="text-[11px] text-[#aaaaaa]">{step.at}</div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </MobileLayout>
    );
}
