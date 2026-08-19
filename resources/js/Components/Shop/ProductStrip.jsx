import { Link } from '@inertiajs/react';
import Icon from './Icon';
import useDragScroll from './useDragScroll';

/**
 * A horizontal-scrolling row of product cards — the shape Home's
 * product-heading sections all share (Rekomendasi Untukmu, Produk Kesehatan
 * Terbaru, Produk Terlaris Kami). Kept separate from the portrait grid tile
 * (`ProductCard`, used by Shop/Wishlist's 2-column grids) since this one is
 * sized for a scrolling strip, not a grid cell.
 *
 * @param {{ products: { id: string, name: string, image: string, price: string, rating: string }[] }} props
 */
export default function ProductStrip({ products }) {
    const drag = useDragScroll();

    return (
        <div
            {...drag}
            className={`flex gap-3 overflow-x-auto px-3.5 pb-1 scrollbar-none ${drag.className}`}
        >
            {products.map((product) => (
                <Link
                    key={product.id}
                    href="/ui/product-detail"
                    className="w-[125px] shrink-0 overflow-hidden rounded-lg border border-line bg-white"
                >
                    <div className="relative h-[125px] w-full overflow-hidden bg-white">
                        <img
                            src={product.image}
                            alt={product.name}
                            className="h-full w-full object-cover"
                        />

                        <div className="absolute left-[5px] top-[5px] flex items-center gap-0.5 bg-white/90 px-1.5 py-0.5">
                            <Icon name="star" size={11} className="text-star" />
                            <span className="text-[9px] font-bold">{product.rating}</span>
                        </div>
                    </div>

                    <div className="px-2 py-2">
                        <div className="truncate text-[11px] text-[#333333]">{product.name}</div>
                        <div className="text-[11px] font-bold text-brand">{product.price}</div>
                    </div>
                </Link>
            ))}
        </div>
    );
}
