import { useState } from 'react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import BenefitsGrid from '@/Components/Shop/BenefitsGrid';
import BrandStrip from '@/Components/Shop/BrandStrip';
import CategoryShortcuts from '@/Components/Shop/CategoryShortcuts';
import HeroCarousel from '@/Components/Shop/HeroCarousel';
import IconLink from '@/Components/Shop/IconLink';
import ProductStrip from '@/Components/Shop/ProductStrip';
import PromoBanner from '@/Components/Shop/PromoBanner';
import SearchBarTrigger from '@/Components/Shop/SearchBarTrigger';
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
                <div className="bg-brand">
                    <AppBar
                        brand
                        tone="brand"
                        actions={
                            <IconLink name="cart" href="/ui/cart" label="Keranjang" badge={cartCount} />
                        }
                    />

                    <div className="px-3.5 pb-3">
                        <SearchBarTrigger onOpen={() => setSearching(true)} />
                    </div>
                </div>
            }
            footer={<TabBar active="home" />}
        >
            <div className="flex-1 overflow-y-auto pb-[70px]">
                <HeroCarousel />

                <CategoryShortcuts />

                <div>
                    <SectionHeading
                        title="Rekomendasi Untukmu"
                        action="Lihat semua"
                        actionHref="/ui/shop"
                        className="px-3.5"
                    />
                    <ProductStrip products={recommended} />
                </div>

                <div>
                    <SectionHeading
                        title="Produk Kesehatan Terbaru"
                        action="Lihat semua"
                        actionHref="/ui/shop"
                        className="px-3.5"
                    />
                    <ProductStrip products={newArrivals} />
                </div>

                <div>
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
                    className="mt-6"
                />

                <div>
                    <SectionHeading title="Brand Terlaris" className="px-3.5" />
                    <BrandStrip brands={topBrands} />
                </div>

                <div>
                    <SectionHeading title="Keuntungan Belanja di Inofarma" className="px-3.5" />
                    <BenefitsGrid />
                </div>
            </div>

            <SearchOverlay open={searching} onClose={() => setSearching(false)} />
        </MobileLayout>
    );
}
