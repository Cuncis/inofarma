import { useState } from 'react';
import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import IconButton from '@/Components/Shop/IconButton';
import IconLink from '@/Components/Shop/IconLink';
import SearchOverlay from '@/Components/Shop/SearchOverlay';
import TabBar from '@/Components/Shop/TabBar';
import { categories } from '@/Components/Shop/data';

export default function Categories() {
    const [searching, setSearching] = useState(false);

    return (
        <MobileLayout
            title="Kategori"
            header={
                <AppBar
                    brand
                    actions={
                        <>
                            <IconButton
                                name="search"
                                onClick={() => setSearching(true)}
                                label="Cari"
                            />
                            <IconLink name="bag" href="/ui/cart" label="Keranjang" />
                        </>
                    }
                />
            }
            footer={<TabBar active="home" />}
        >
            <div className="flex-1 overflow-y-auto px-3.5 pb-[70px] pt-3.5">
                <div className="grid grid-cols-2 gap-3">
                    {categories.map((category) => (
                        <Link
                            key={category.name}
                            href="/ui/shop"
                            className="relative block h-[130px] overflow-hidden"
                        >
                            <img
                                src={category.image}
                                alt={category.name}
                                className="absolute inset-0 h-full w-full object-cover"
                            />

                            <div className="absolute inset-x-0 bottom-0 flex justify-center pb-2.5">
                                <span className="bg-white px-4 py-1 text-[11px] font-bold uppercase tracking-[0.5px]">
                                    {category.name}
                                </span>
                            </div>
                        </Link>
                    ))}
                </div>
            </div>

            <SearchOverlay open={searching} onClose={() => setSearching(false)} />
        </MobileLayout>
    );
}
