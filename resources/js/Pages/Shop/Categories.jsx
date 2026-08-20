import { useState } from 'react';
import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import IconButton from '@/Components/Shop/IconButton';
import IconLink from '@/Components/Shop/IconLink';
import SearchOverlay from '@/Components/Shop/SearchOverlay';
import TabBar from '@/Components/Shop/TabBar';
import useCartCount from '@/Components/Shop/useCartCount';
import { shopCategories } from '@/Components/Shop/data';

export default function Categories() {
    const cartCount = useCartCount();
    const [searching, setSearching] = useState(false);

    return (
        <MobileLayout
            title="Kategori"
            header={
                <AppBar
                    brand
                    tone="brand"
                    actions={
                        <>
                            <IconButton
                                name="search"
                                onClick={() => setSearching(true)}
                                label="Cari"
                            />
                            <IconLink name="cart" href="/ui/cart" label="Keranjang" badge={cartCount} />
                        </>
                    }
                />
            }
            footer={<TabBar active="home" />}
        >
            <div className="flex-1 overflow-y-auto px-3.5 pb-[70px] pt-3.5">
                <div className="grid grid-cols-2 gap-3">
                    {shopCategories.map((category) => (
                        <Link
                            key={category.name}
                            href="/ui/shop"
                            className="flex h-[130px] flex-col items-center justify-center gap-2.5 rounded-lg border border-line bg-white p-3 text-center"
                        >
                            <img
                                src={category.image}
                                alt={category.name}
                                className="h-16 w-16 object-contain"
                            />

                            <span className="text-[11px] font-bold uppercase tracking-[0.5px] text-ink">
                                {category.name}
                            </span>
                        </Link>
                    ))}
                </div>
            </div>

            <SearchOverlay open={searching} onClose={() => setSearching(false)} />
        </MobileLayout>
    );
}
