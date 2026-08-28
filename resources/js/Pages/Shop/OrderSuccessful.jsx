import MobileLayout from '@/Layouts/MobileLayout';
import Button from '@/Components/Shop/Button';
import { asset } from '@/Components/Shop/data';

/**
 * @param {{ orderNumber?: string|null }} props
 */
export default function OrderSuccessful({ orderNumber }) {
    return (
        <MobileLayout title="Pesanan Berhasil" background="bg-canvas">
            <div className="flex flex-1 flex-col items-center justify-center overflow-y-auto px-6 py-7 text-center">
                <img src={asset.logo('white')} alt="Inofarma" className="mb-[18px] h-8 w-auto" />

                <img
                    src={asset.other('02')}
                    alt=""
                    className="mx-auto mb-4 h-[175px] w-[175px] object-contain"
                />

                <h2 className="mb-2.5 font-display text-[22px]">Pesanan Anda diterima!</h2>

                <p className="mb-[22px] text-[13px] leading-relaxed text-muted">
                    {orderNumber ? (
                        <>
                            Pesanan <strong>#{orderNumber}</strong> telah kami terima dan
                            <br />
                            sedang diproses.
                        </>
                    ) : (
                        <>
                            Pesanan Anda telah kami terima dan
                            <br />
                            sedang diproses.
                        </>
                    )}
                </p>

                <Button
                    href={orderNumber ? `/ui/track-order/${orderNumber}` : '/ui/order-history'}
                    className="mb-2"
                >
                    Lacak Pesanan
                </Button>

                <Button href="/ui/profile" variant="outline">
                    Buka Profil Saya
                </Button>
            </div>
        </MobileLayout>
    );
}
