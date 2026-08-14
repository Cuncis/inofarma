import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import IconLink from '@/Components/BeShop/IconLink';
import ProductCard from '@/Components/BeShop/ProductCard';
import TabBar from '@/Components/BeShop/TabBar';
import { shopProducts } from '@/Components/BeShop/data';

export default function Shop() {
    return (
        <MobileLayout
            title="Shop"
            header={
                <AppBar
                    brand
                    actions={
                        <>
                            <IconLink name="search" href="/ui/filter" label="Filter" />
                            <IconLink name="bag" href="/ui/cart" label="Cart" />
                        </>
                    }
                />
            }
            footer={<TabBar active="home" />}
        >
            <div className="flex-1 overflow-y-auto p-3 pb-[70px]">
                <div className="grid grid-cols-2 gap-3">
                    {shopProducts.map((product) => (
                        <ProductCard key={product.name} product={product} />
                    ))}
                </div>
            </div>
        </MobileLayout>
    );
}
