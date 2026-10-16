import { Link } from '@inertiajs/react';
import DesktopLayout from '@/Layouts/DesktopLayout';
import BenefitsGrid from './BenefitsGrid';
import BrandStrip from './BrandStrip';
import Carousel from './Carousel';
import CategoryShortcuts from './CategoryShortcuts';
import DesktopHeader from './DesktopHeader';
import HeroCarousel from './HeroCarousel';
import NearestPharmacies from './NearestPharmacies';
import ProductGrid from './ProductGrid';
import TestimonialSlider from './TestimonialSlider';
import { useShopCatalog } from './data';

function ProductSection({ title, products }: { title: string, products: ProductTile[] }) {
    return (
        <section className="mt-10">
            <div className="mb-4 flex items-center justify-between">
                <h2 className="font-display text-[13px] text-brand">{title}</h2>

                <Link href="/shop" className="text-[11px] font-bold text-success">
                    Lihat semua
                </Link>
            </div>

            <ProductGrid products={products} />
        </section>
    );
}

/**
 * Desktop version of the storefront home: full-width header, single centred
 * column, no bottom tab bar. Slides come from the mobile page so both
 * layouts always show the same campaigns.
 */
export default function DesktopHome({ promoSlides, bottomSlides }: {
  promoSlides: { image: string, href: string, alt: string }[],
  bottomSlides: { image: string, href: string, alt: string }[],
}) {
    const { recommended, newArrivals, trendingProducts } = useShopCatalog();

    return (
        <DesktopLayout title="Beranda" header={<DesktopHeader />}>
            <HeroCarousel className="mt-6" />

            <CategoryShortcuts className="mt-6" />

            <Carousel slides={promoSlides} aspect="aspect-1740/396" className="mt-10" />

            <ProductSection title="Rekomendasi Untukmu" products={recommended} />
            <ProductSection title="Produk Kesehatan Terbaru" products={newArrivals} />
            <ProductSection title="Produk Terlaris Kami" products={trendingProducts} />

            <Carousel slides={bottomSlides} aspect="aspect-1920/601" className="mt-12" />

            <section className="mt-10">
                <h2 className="mb-4 font-display text-[19px] text-brand">Brand Terlaris</h2>
                <BrandStrip className="px-0" spread />
            </section>

            <section className="mt-10">
                <h2 className="mb-4 font-display text-[19px] text-brand">Keuntungan Belanja di Inofarma</h2>
                <BenefitsGrid className="grid-cols-9" />
            </section>

            <section className="mt-10">
                <h2 className="mb-4 font-display text-[19px] text-brand">Apotek Inofarma Terdekat</h2>
                <NearestPharmacies className="px-0" desktop />
            </section>

            <section className="mt-10">
                <h2 className="mb-4 font-display text-[19px] text-brand">Testimoni Sobat Ino</h2>
                <TestimonialSlider className="px-0" />
            </section>
        </DesktopLayout>
    );
}
