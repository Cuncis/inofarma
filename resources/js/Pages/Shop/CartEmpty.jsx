import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import IconLink from '@/Components/Shop/IconLink';
import TabBar from '@/Components/Shop/TabBar';
import { asset } from '@/Components/Shop/data';

export default function CartEmpty() {
    return (
        <MobileLayout
            title="Keranjang Kosong"
            header={
                <AppBar
                    tone="white"
                    actions={<IconLink name="user" href="/ui/profile" label="Profil" />}
                />
            }
            footer={<TabBar active="order" />}
        >
            <div className="flex flex-1 flex-col items-center justify-center overflow-y-auto px-6 py-7 text-center">
                <img
                    src={asset.other('03')}
                    alt=""
                    className="mx-auto mb-4 h-[190px] w-[190px] object-contain"
                />

                <h2 className="mb-2.5 font-display text-[22px]">Keranjang Anda kosong!</h2>

                <p className="mb-[22px] text-[13px] leading-relaxed text-muted">
                    Sepertinya Anda belum melakukan
                    <br />
                    pemesanan.
                </p>

                <Button href="/ui/shop">Mulai Belanja</Button>
            </div>
        </MobileLayout>
    );
}
