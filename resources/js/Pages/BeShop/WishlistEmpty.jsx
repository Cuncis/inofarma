import MobileLayout from '@/Layouts/MobileLayout';
import Button from '@/Components/BeShop/Button';
import Icon from '@/Components/BeShop/Icon';
import TabBar from '@/Components/BeShop/TabBar';

export default function WishlistEmpty() {
    return (
        <MobileLayout title="Wishlist Empty" footer={<TabBar active="wishlist" />}>
            <div className="flex flex-1 flex-col items-center justify-center overflow-y-auto px-6 py-7 text-center">
                <Icon name="heart" size={80} className="mb-5 text-brand opacity-20" />

                <p className="mb-[22px] text-sm leading-[1.8] text-muted">
                    Your wishlist is currently empty!
                    <br />
                    Start adding products to your wishlist.
                </p>

                <Button href="/ui/shop">Shop Now</Button>
            </div>
        </MobileLayout>
    );
}
