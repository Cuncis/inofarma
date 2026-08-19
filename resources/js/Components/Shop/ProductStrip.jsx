import { Link } from '@inertiajs/react';
import Icon from './Icon';

/**
 * A horizontal-scrolling row of compact product tiles — the shape Home's
 * product-heading sections all share (Rekomendasi Untukmu, Produk Kesehatan
 * Terbaru, Produk Terlaris Kami). Kept separate from the portrait grid tile
 * (`ProductCard`, used by Shop/Wishlist's 2-column grids) since this one is
 * sized for a scrolling strip, not a grid cell.
 *
 * @param {{ products: { id: string, name: string, image: string, price: string, rating: string }[] }} props
 */
export default function ProductStrip({ products }) {
    return (
        <div className="flex gap-2.5 overflow-x-auto px-3.5 scrollbar-none">
            {products.map((product) => (
                <Link key={product.id} href="/ui/product-detail" className="w-[115px] shrink-0">
                    <div className="relative h-[145px] w-[115px] overflow-hidden bg-white">
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

                    <div className="mt-[5px] text-[11px] text-[#333333]">{product.name}</div>
                    <div className="text-[11px] font-bold text-brand">{product.price}</div>
                </Link>
            ))}
        </div>
    );
}
