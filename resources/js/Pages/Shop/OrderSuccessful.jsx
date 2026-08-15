import MobileLayout from '@/Layouts/MobileLayout';
import Button from '@/Components/Shop/Button';
import { asset } from '@/Components/Shop/data';

export default function OrderSuccessful() {
    return (
        <MobileLayout title="Pesanan Berhasil" background="bg-blush">
            <div className="flex flex-1 flex-col items-center justify-center overflow-y-auto px-6 py-7 text-center">
                <div className="mb-[18px] font-display text-xl tracking-[2px]">INOFARMA</div>

                <img
                    src={asset.other('02')}
                    alt=""
                    className="mx-auto mb-4 h-[175px] w-[175px] object-contain"
                />

                <h2 className="mb-2.5 font-display text-[22px]">Pesanan Anda diterima!</h2>

                <p className="mb-[22px] text-[13px] leading-relaxed text-muted">
                    Pesanan Anda telah kami terima dan
                    <br />
                    sedang diproses.
                </p>

                <Button href="/ui/track-order" className="mb-2">
                    Lacak Pesanan
                </Button>

                <Button href="/ui/profile" variant="outline">
                    Buka Profil Saya
                </Button>
            </div>
        </MobileLayout>
    );
}
