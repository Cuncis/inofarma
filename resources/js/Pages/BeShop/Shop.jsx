import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Icon from '@/Components/BeShop/Icon';
import ProductCard from '@/Components/BeShop/ProductCard';
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
                            <Icon name="search" size={19} />
                            <Icon name="bag" size={19} />
                        </>
                    }
                />
            }
        >
            <div className="flex-1 overflow-y-auto p-3">
                <div className="grid grid-cols-2 gap-3">
                    {shopProducts.map((product) => (
                        <ProductCard key={product.name} product={product} />
                    ))}
                </div>
            </div>
        </MobileLayout>
    );
}
