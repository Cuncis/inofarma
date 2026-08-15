import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Icon from '@/Components/BeShop/Icon';
import IconLink from '@/Components/BeShop/IconLink';
import ProductCard from '@/Components/BeShop/ProductCard';
import PromoBanner from '@/Components/BeShop/PromoBanner';
import SectionHeading from '@/Components/BeShop/SectionHeading';
import TabBar from '@/Components/BeShop/TabBar';
import { newArrivals, trendingProducts } from '@/Components/BeShop/data';

export default function Home() {
    return (
        <MobileLayout
            title="Beranda"
            header={
                <AppBar
                    brand
                    actions={
                        <>
                            <IconLink name="search" href="/ui/shop" label="Cari" />
                            <IconLink name="bag" href="/ui/cart" label="Keranjang" />
                        </>
                    }
                />
            }
            footer={<TabBar active="home" />}
        >
            <div className="flex-1 overflow-y-auto">
                <PromoBanner
                    href="/ui/categories"
                    eyebrow="Apotek online"
                    title={'Obat & vitamin\nsampai hari ini'}
                    caption="Gratis ongkir untuk pembelian di atas Rp 750.000"
                    cta="Lihat kategori"
                    icon="bag"
                />

                <div className="mt-3.5">
                    <SectionHeading
                        title="Produk Terlaris"
                        action="Lihat semua"
                        actionHref="/ui/shop"
                        className="px-3.5"
                    />

                    <div className="flex gap-2.5 overflow-x-auto px-3.5 scrollbar-none">
                        {trendingProducts.map((product) => (
                            <Link
                                key={product.name}
                                href="/ui/product-detail"
                                className="w-[115px] shrink-0"
                            >
                                <div className="relative h-[145px] w-[115px] overflow-hidden bg-[#f5f5f5]">
                                    <img
                                        src={product.image}
                                        alt={product.name}
                                        className="h-full w-full object-cover"
                                    />

                                    <div className="absolute left-[5px] top-[5px] flex items-center gap-0.5 bg-white/90 px-1.5 py-0.5">
                                        <Icon name="star" size={11} className="text-star" />
                                        <span className="text-[9px] font-bold">
                                            {product.rating}
                                        </span>
                                    </div>
                                </div>

                                <div className="mt-[5px] text-[11px] text-[#333333]">
                                    {product.name}
                                </div>
                                <div className="text-[11px] font-bold text-brand">
                                    {product.price}
                                </div>
                            </Link>
                        ))}
                    </div>
                </div>

                <PromoBanner
                    href="/ui/shop"
                    eyebrow="Promo bulan ini"
                    title={'Diskon hingga 20%\nuntuk suplemen'}
                    caption="Pakai kode HEMAT15 di halaman keranjang"
                    cta="Belanja sekarang"
                    icon="promo"
                    tone="success"
                    className="mt-3"
                />

                <div className="mt-3 px-3.5 pb-[70px]">
                    <SectionHeading
                        title="Produk Terbaru"
                        action="Lihat semua"
                        actionHref="/ui/shop"
                    />

                    <div className="grid grid-cols-2 gap-3">
                        {newArrivals.map((product) => (
                            <ProductCard key={product.name} product={product} />
                        ))}
                    </div>
                </div>
            </div>
        </MobileLayout>
    );
}
