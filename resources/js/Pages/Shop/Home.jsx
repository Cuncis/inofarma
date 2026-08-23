import { useState } from 'react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import BenefitsGrid from '@/Components/Shop/BenefitsGrid';
import BrandStrip from '@/Components/Shop/BrandStrip';
import Carousel from '@/Components/Shop/Carousel';
import CategoryShortcuts from '@/Components/Shop/CategoryShortcuts';
import HeroCarousel from '@/Components/Shop/HeroCarousel';
import IconLink from '@/Components/Shop/IconLink';
import ProductStrip from '@/Components/Shop/ProductStrip';
import SearchBarTrigger from '@/Components/Shop/SearchBarTrigger';
import SectionHeading from '@/Components/Shop/SectionHeading';
import SearchOverlay from '@/Components/Shop/SearchOverlay';
import TabBar from '@/Components/Shop/TabBar';
import Testimonials from '@/Components/Shop/Testimonials';
import useCartCount from '@/Components/Shop/useCartCount';
import { useShopCatalog } from '@/Components/Shop/data';

const PROMO_SLIDES = [
    {
        image: '/media/images/promo/sehat-ga-mesti-mahal.png',
        href: '/ui/signup',
        alt: 'Sehat ga mesti mahal — ayo daftar member Sobat Ino',
    },
    {
        image: '/media/images/promo/harga-sobat-produk-lengkap.png',
        href: '/ui/signup',
        alt: 'Harga Sobat, Produk Lengkap — gabung Sobat Ino sekarang',
    },
];

const BOTTOM_SLIDES = [
    {
        image: '/media/images/promo/pengiriman-instan-24-jam.png',
        href: '/ui/cabang-kami',
        alt: 'Pengiriman instan dengan layanan antar 24 jam',
    },
    {
        image: '/media/images/promo/temukan-cabang-terdekat.png',
        href: '/ui/cabang-kami',
        alt: 'Temukan cabang Inofarma terdekat',
    },
    {
        image: '/media/images/promo/selalu-lebih-hemat.png',
        href: '/ui/shop',
        alt: 'Selalu lebih hemat, lebih lengkap',
    },
];

export default function Home() {
    const { recommended, newArrivals, trendingProducts } = useShopCatalog();
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
            <div className="flex-1 overflow-y-auto pb-5">
                <HeroCarousel />

                <CategoryShortcuts />

                <Carousel
                    slides={PROMO_SLIDES}
                    aspect="aspect-[1740/396]"
                    className="mx-3.5 mb-3.5 mt-1"
                />

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

                <Carousel
                    slides={BOTTOM_SLIDES}
                    aspect="aspect-[1920/601]"
                    className="mx-3.5 mt-6"
                />

                <div>
                    <SectionHeading title="Brand Terlaris" className="px-3.5" />
                    <BrandStrip />
                </div>

                <div>
                    <SectionHeading title="Testimoni Sobat Ino" className="px-3.5" />
                    <Testimonials />
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
