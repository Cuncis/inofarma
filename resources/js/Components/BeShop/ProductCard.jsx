import Icon from './Icon';

/**
 * @typedef {object} Product
 * @property {string} name
 * @property {string} image
 * @property {string} price
 * @property {string} [oldPrice]
 * @property {string} rating
 */

/**
 * Portrait product tile used by the grids on Home, Shop and Wishlist.
 *
 * @param {{ product: Product }} props
 */
export default function ProductCard({ product }) {
    return (
        <div>
            <div className="relative aspect-[3/4] overflow-hidden bg-[#f7f7f7]">
                <img
                    src={product.image}
                    alt={product.name}
                    className="h-full w-full object-cover"
                />

                <div className="absolute left-[7px] top-[7px] flex items-center gap-[3px] bg-white/90 px-[7px] py-[3px]">
                    <Icon name="star" size={11} className="text-star" />
                    <span className="text-[10px] font-bold">{product.rating}</span>
                </div>

                <div className="absolute bottom-8 right-0 flex h-8 w-8 items-center justify-center bg-white/90 text-faint">
                    <Icon name="heart" size={18} />
                </div>

                <div className="absolute bottom-0 right-0 flex h-8 w-8 items-center justify-center bg-white/90 text-faint">
                    <Icon name="bagSimple" size={18} />
                </div>
            </div>

            <div className="mb-[3px] mt-1.5 truncate text-xs text-[#333333]">
                {product.name}
            </div>

            <div className="flex items-center gap-[5px]">
                {product.oldPrice ? (
                    <span className="text-[9px] text-faint line-through">
                        {product.oldPrice}
                    </span>
                ) : null}

                <span
                    className={`text-xs font-bold ${
                        product.oldPrice ? 'text-brand' : 'text-muted'
                    }`}
                >
                    {product.price}
                </span>
            </div>
        </div>
    );
}
