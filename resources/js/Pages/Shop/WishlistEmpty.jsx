import MobileLayout from '@/Layouts/MobileLayout';
import Button from '@/Components/Shop/Button';
import Icon from '@/Components/Shop/Icon';
import TabBar from '@/Components/Shop/TabBar';

export default function WishlistEmpty() {
    return (
        <MobileLayout title="Favorit Kosong" footer={<TabBar active="wishlist" />}>
            <div className="flex flex-1 flex-col items-center justify-center overflow-y-auto px-6 py-7 text-center">
                <Icon name="heart" size={80} className="mb-5 text-brand opacity-20" />

                <p className="mb-[22px] text-sm leading-[1.8] text-muted">
                    Daftar favorit Anda masih kosong!
                    <br />
                    Yuk, tambahkan produk favorit Anda.
                </p>

                <Button href="/ui/shop">Mulai Belanja</Button>
            </div>
        </MobileLayout>
    );
}
