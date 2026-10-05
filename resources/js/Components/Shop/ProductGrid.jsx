import { Link } from '@inertiajs/react';

/**
 * Desktop product table: six bordered cells per row sharing their edges (only
 * `border-r`/`border-b` per cell, `border-l`/`border-t` on the grid, the same
 * trick as `CategoryShortcuts`), each with photo, brand, name, price and a
 * full-width action. Out-of-stock items get a disabled grey button.
 *
 * The action opens the product screen, where the shopper picks a branch
 * before anything goes into the cart, same as the mobile cards.
 *
 * @param {{ products: { id: string, name: string, image: string, price: string, brand: string, soldOut: boolean }[] }} props
 */
export default function ProductGrid({ products }) {
    return (
        <div className="grid grid-cols-6 border-l border-t border-line bg-white">
            {products.map((product) => (
                <div key={product.id} className="flex flex-col border-b border-r border-line p-4">
                    <Link href="/ui/product-detail" className="block">
                        <img
                            src={product.image}
                            alt={product.name}
                            className="mb-5 h-32 w-full object-contain"
                        />

                        <div className="text-[10px] uppercase text-muted">{product.brand}</div>

                        <div className="mt-1.5 line-clamp-2 min-h-[2.5rem] text-xs font-medium leading-5 text-brand">
                            {product.name}
                        </div>

                        <div className="mb-4 mt-1.5 text-base text-success">{product.price}</div>
                    </Link>

                    {product.soldOut ? (
                        <span className="mt-auto flex h-8 items-center justify-center bg-[#8b929a] text-xs font-bold text-white">
                            Terjual habis
                        </span>
                    ) : (
                        <Link
                            href="/ui/product-detail"
                            className="mt-auto flex h-8 items-center justify-center bg-success text-xs font-bold text-white"
                        >
                            Tambah
                        </Link>
                    )}
                </div>
            ))}
        </div>
    );
}
