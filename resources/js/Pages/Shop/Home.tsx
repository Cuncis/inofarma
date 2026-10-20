import { useState } from 'react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import BenefitsGrid from '@/Components/Shop/BenefitsGrid';
import HomeInfo from '@/Components/Shop/HomeInfo';
import NearestPharmacies from '@/Components/Shop/NearestPharmacies';
import TestimonialSlider from '@/Components/Shop/TestimonialSlider';
import BrandStrip from '@/Components/Shop/BrandStrip';
import Carousel from '@/Components/Shop/Carousel';
import CategoryShortcuts from '@/Components/Shop/CategoryShortcuts';
import DesktopHome from '@/Components/Shop/DesktopHome';
import HeroCarousel from '@/Components/Shop/HeroCarousel';
import IconLink from '@/Components/Shop/IconLink';
import ProductStrip from '@/Components/Shop/ProductStrip';
import SearchBarTrigger from '@/Components/Shop/SearchBarTrigger';
import SectionHeading from '@/Components/Shop/SectionHeading';
import SearchOverlay from '@/Components/Shop/SearchOverlay';
import TabBar from '@/Components/Shop/TabBar';
import useCartCount from '@/Components/Shop/useCartCount';
import useIsDesktop from '@/Components/Shop/useIsDesktop';
import { useShopCatalog } from '@/Components/Shop/data';

/**
 * `content` is edited in the admin under Konten Website > Beranda; `pharmacies`
 * are the branches with a photo, from Cabang.
 */
export default function Home({ content, pharmacies }: { content: HomeContent; pharmacies: BranchCard[] }) {
    const { recommended, newArrivals, trendingProducts } = useShopCatalog();
    const cartCount = useCartCount();
    const [searching, setSearching] = useState(false);
    const isDesktop = useIsDesktop();

    if (isDesktop) {
        return <DesktopHome content={content} pharmacies={pharmacies} />;
    }

    return (
        <MobileLayout
            title="Beranda"
            header={
                <div className="bg-brand">
                    <AppBar
                        brand
                        tone="brand"
                        actions={
                            <IconLink id="cart-icon-target" name="cart" href="/cart" label="Keranjang" badge={cartCount} />
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
                <HeroCarousel slides={content.hero} />

                <CategoryShortcuts allProductsIcon={content.allProductsIcon} />

                <Carousel
                    slides={content.promo}
                    aspect="aspect-1740/396"
                    className="mx-3.5 mb-3.5 mt-1"
                />

                <div>
                    <SectionHeading
                        title={content.headings.recommended}
                        action="Lihat semua"
                        actionHref="/shop"
                        className="px-3.5"
                    />
                    <ProductStrip products={recommended} />
                </div>

                <div>
                    <SectionHeading
                        title={content.headings.newArrivals}
                        action="Lihat semua"
                        actionHref="/shop"
                        className="px-3.5"
                    />
                    <ProductStrip products={newArrivals} />
                </div>

                <div>
                    <SectionHeading
                        title={content.headings.trending}
                        action="Lihat semua"
                        actionHref="/shop"
                        className="px-3.5"
                    />
                    <ProductStrip products={trendingProducts} />
                </div>

                <Carousel
                    slides={content.bottom}
                    aspect="aspect-1920/601"
                    className="mx-3.5 mt-6"
                />

                {content.brands.length > 0 ? (
                    <div>
                        <SectionHeading title={content.headings.brands} className="px-3.5" />
                        <BrandStrip brands={content.brands} />
                    </div>
                ) : null}

                {content.benefits.length > 0 ? (
                    <div>
                        <SectionHeading title={content.headings.benefits} className="px-3.5" />
                        <BenefitsGrid benefits={content.benefits} />
                    </div>
                ) : null}

                {pharmacies.length > 0 ? (
                    <div>
                        <SectionHeading title={content.headings.nearest} className="px-3.5" />
                        <NearestPharmacies pharmacies={pharmacies} />
                    </div>
                ) : null}

                {content.testimonials.length > 0 ? (
                    <div>
                        <SectionHeading title={content.headings.testimonials} className="px-3.5" />
                        <TestimonialSlider testimonials={content.testimonials} />
                    </div>
                ) : null}

                <HomeInfo content={content} />
            </div>

            <SearchOverlay open={searching} onClose={() => setSearching(false)} />
        </MobileLayout>
    );
}
