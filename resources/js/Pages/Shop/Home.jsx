import { useState } from 'react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import BenefitsGrid from '@/Components/Shop/BenefitsGrid';
import BrandStrip from '@/Components/Shop/BrandStrip';
import CategoryShortcuts from '@/Components/Shop/CategoryShortcuts';
import HeroCarousel from '@/Components/Shop/HeroCarousel';
import IconButton from '@/Components/Shop/IconButton';
import IconLink from '@/Components/Shop/IconLink';
import ProductStrip from '@/Components/Shop/ProductStrip';
import PromoBanner from '@/Components/Shop/PromoBanner';
import SectionHeading from '@/Components/Shop/SectionHeading';
import SearchOverlay from '@/Components/Shop/SearchOverlay';
import TabBar from '@/Components/Shop/TabBar';
import useCartCount from '@/Components/Shop/useCartCount';
import { useShopCatalog } from '@/Components/Shop/data';

export default function Home() {
    const { recommended, newArrivals, trendingProducts, topBrands } = useShopCatalog();
    const cartCount = useCartCount();
    const [searching, setSearching] = useState(false);

    return (
        <MobileLayout
            title="Beranda"
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
            <div className="flex-1 overflow-y-auto pb-[70px]">
                <HeroCarousel />

                <CategoryShortcuts />

                <div className="mt-3.5">
                    <SectionHeading
                        title="Rekomendasi Untukmu"
                        action="Lihat semua"
                        actionHref="/ui/shop"
                        className="px-3.5"
                    />
                    <ProductStrip products={recommended} />
                </div>

                <div className="mt-3.5">
                    <SectionHeading
                        title="Produk Kesehatan Terbaru"
                        action="Lihat semua"
                        actionHref="/ui/shop"
                        className="px-3.5"
                    />
                    <ProductStrip products={newArrivals} />
                </div>

                <div className="mt-3.5">
                    <SectionHeading
                        title="Produk Terlaris Kami"
                        action="Lihat semua"
                        actionHref="/ui/shop"
                        className="px-3.5"
                    />
                    <ProductStrip products={trendingProducts} />
                </div>

                <PromoBanner
                    href="/ui/shop"
                    eyebrow="Promo bulan ini"
                    title={'Diskon hingga 20%\nuntuk suplemen'}
                    caption="Pakai kode HEMAT15 di halaman keranjang"
                    cta="Belanja sekarang"
                    icon="promo"
                    tone="success"
                    className="mt-3.5"
                />

                <div className="mt-3.5">
                    <SectionHeading title="Brand Terlaris" className="px-3.5" />
                    <BrandStrip brands={topBrands} />
                </div>

                <div className="mt-3.5">
                    <SectionHeading title="Keuntungan Belanja di Inofarma" className="px-3.5" />
                    <BenefitsGrid />
                </div>
            </div>

            <SearchOverlay open={searching} onClose={() => setSearching(false)} />
        </MobileLayout>
    );
}
