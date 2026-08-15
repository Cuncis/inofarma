import { useState } from 'react';
import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import IconLink from '@/Components/BeShop/IconLink';
import ProductCard from '@/Components/BeShop/ProductCard';
import TabBar from '@/Components/BeShop/TabBar';
import { wishlistProducts } from '@/Components/BeShop/data';

export default function Wishlist() {
    const [products, setProducts] = useState(wishlistProducts);

    /**
     * Un-hearting the last product hands over to the empty-wishlist screen.
     *
     * @param {string} name
     */
    const remove = (name) => {
        const next = products.filter((product) => product.name !== name);

        if (next.length === 0) {
            router.visit('/ui/wishlist-empty');

            return;
        }

        setProducts(next);
    };

    return (
        <MobileLayout
            title="Favorit"
            header={
                <AppBar
                    brand
                    title="Favorit"
                    actions={<IconLink name="bag" href="/ui/cart" label="Keranjang" />}
                />
            }
            footer={<TabBar active="wishlist" />}
        >
            <div className="flex-1 overflow-y-auto px-3.5 pb-[70px] pt-3.5">
                <div className="grid grid-cols-2 gap-3">
                    {products.map((product) => (
                        <ProductCard
                            key={product.name}
                            product={product}
                            wishlisted
                            onRemove={() => remove(product.name)}
                        />
                    ))}
                </div>
            </div>
        </MobileLayout>
    );
}
