import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Icon from '@/Components/BeShop/Icon';
import ProductCard from '@/Components/BeShop/ProductCard';
import SectionHeading from '@/Components/BeShop/SectionHeading';
import TabBar from '@/Components/BeShop/TabBar';
import { asset, newArrivals, trendingProducts } from '@/Components/BeShop/data';

export default function Home() {
    return (
        <MobileLayout
            title="Home"
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
            footer={<TabBar active="home" />}
        >
            <div className="flex-1 overflow-y-auto">
                <img src={asset.banner('01')} alt="" className="w-full" />

                <div className="mt-3.5">
                    <SectionHeading
                        title="Trending Products"
                        action="View all"
                        className="px-3.5"
                    />

                    <div className="flex gap-2.5 overflow-x-auto px-3.5 scrollbar-none">
                        {trendingProducts.map((product) => (
                            <div key={product.name} className="w-[115px] shrink-0">
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
                            </div>
                        ))}
                    </div>
                </div>

                <img src={asset.banner('02')} alt="" className="mt-3 w-full" />

                <div className="mt-3 px-3.5 pb-[70px]">
                    <SectionHeading title="New Arrivals" action="View all" />

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
