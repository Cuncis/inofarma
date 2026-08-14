import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Icon from '@/Components/BeShop/Icon';
import ProductCard from '@/Components/BeShop/ProductCard';
import TabBar from '@/Components/BeShop/TabBar';
import { wishlistProducts } from '@/Components/BeShop/data';

export default function Wishlist() {
    return (
        <MobileLayout
            title="Wishlist"
            header={
                <AppBar brand title="Wishlist" actions={<Icon name="bag" size={19} />} />
            }
            footer={<TabBar active="wishlist" />}
        >
            <div className="flex-1 overflow-y-auto px-3.5 pb-[70px] pt-3.5">
                <div className="grid grid-cols-2 gap-3">
                    {wishlistProducts.map((product) => (
                        <ProductCard key={product.name} product={product} />
                    ))}
                </div>
            </div>
        </MobileLayout>
    );
}
