import { Link } from '@inertiajs/react';
import Badge from './Badge';
import RowActions from './RowActions';
import { money, statusTone } from './data';

/**
 * Card grid for the product list's grid view.
 *
 * Renders the same records and the same row actions as the table — only the
 * layout differs, so switching view never changes what you can do.
 *
 * @param {{
 *   products: object[],
 *   onDelete: (product: object) => void,
 *   empty?: string,
 * }} props
 */
export default function ProductGridView({ products, onDelete, empty = 'Produk tidak ditemukan.' }) {
    if (products.length === 0) {
        return (
            <p className="px-5 py-12 text-center text-[13px] text-admin-muted dark:text-admin-dark-muted">
                {empty}
            </p>
        );
    }

    return (
        <div className="grid gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
            {products.map((product) => (
                <div
                    key={product.id}
                    className="overflow-hidden rounded-xl border border-admin-border dark:border-admin-dark-border"
                >
                    <Link
                        href={`/admin/produk/${product.id}`}
                        className="flex h-36 items-center justify-center bg-admin-hover p-5 dark:bg-admin-dark-hover"
                    >
                        <img
                            src={product.image}
                            alt={product.name}
                            className="h-full max-w-full object-contain"
                        />
                    </Link>

                    <div className="p-4">
                        <div className="mb-2 flex items-start justify-between gap-2">
                            <Link
                                href={`/admin/produk/${product.id}`}
                                className="text-[13px] font-semibold text-admin-heading hover:text-brand dark:text-admin-dark-heading"
                            >
                                {product.name}
                            </Link>

                            <Badge tone={statusTone(product.status)}>{product.status}</Badge>
                        </div>

                        <p className="mb-3 text-xs text-admin-muted dark:text-admin-dark-muted">
                            {product.category} · {product.id}
                        </p>

                        <div className="flex items-center justify-between gap-2">
                            <span>
                                {product.oldPrice ? (
                                    <span className="mr-1.5 text-xs text-admin-muted line-through dark:text-admin-dark-muted">
                                        {money(product.oldPrice)}
                                    </span>
                                ) : null}
                                <span className="text-[15px] font-bold text-admin-heading dark:text-admin-dark-heading">
                                    {money(product.price)}
                                </span>
                            </span>

                            <span className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                Stok {product.stock}
                            </span>
                        </div>

                        <div className="mt-3 border-t border-admin-border pt-2 dark:border-admin-dark-border">
                            <RowActions
                                label={product.name}
                                viewHref={`/admin/produk/${product.id}`}
                                editHref={`/admin/produk/${product.id}/ubah`}
                                onDelete={() => onDelete(product)}
                            />
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
}
