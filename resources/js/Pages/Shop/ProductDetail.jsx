import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Icon from '@/Components/Shop/Icon';
import BranchPicker from '@/Components/Shop/BranchPicker';
import FlashBanner from '@/Components/Shop/FlashBanner';
import { DrugInfoSection } from '@/Components/Shop/DrugInfo';
import IconLink from '@/Components/Shop/IconLink';
import ProductGallery from '@/Components/Shop/ProductGallery';
import Rating from '@/Components/Shop/Rating';
import ReviewCard from '@/Components/Shop/ReviewCard';
import useCartCount from '@/Components/Shop/useCartCount';
import { findProduct, money, reviews, useShopCatalog } from '@/Components/Shop/data';

export default function ProductDetail() {
    const { products } = useShopCatalog();
    const { url } = usePage();

    // Which product to show comes from `?id=`, so a tile anywhere in the shop
    // can link straight here. Falls back to the first product when absent.
    const requested = new URLSearchParams(url.split('?')[1] ?? '').get('id');
    const product = findProduct(products, requested ?? undefined);

    const [variant, setVariant] = useState(product?.variants[0]);
    const [liked, setLiked] = useState(false);
    const cartCount = useCartCount();

    if (! product) {
        return (
            <MobileLayout title="Detail Produk">
                <div className="flex flex-1 items-center justify-center p-8 text-center text-sm text-muted">
                    Produk tidak ditemukan.
                </div>
            </MobileLayout>
        );
    }

    return (
        <MobileLayout
            title="Detail Produk"
            header={
                <AppBar
                    title="Detail Produk"
                    back="/ui/shop"
                    tone="brand"
                    actions={
                        <>
                            <button
                                type="button"
                                onClick={() => setLiked((current) => ! current)}
                                aria-label={liked ? 'Hapus dari favorit' : 'Tambahkan ke favorit'}
                                className={liked ? 'text-danger' : 'text-white'}
                            >
                                <Icon name={liked ? 'heartFilled' : 'heart'} size={20} />
                            </button>

                            <IconLink name="cart" href="/ui/cart" label="Keranjang" badge={cartCount} />
                        </>
                    }
                />
            }
        >
            <ProductGallery
                images={product.images ?? [{ path: product.image, alt: product.name }]}
                badge={
                    product.prescription ? (
                        <span className="absolute bottom-3 left-3 bg-warning px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-ink">
                            Perlu resep
                        </span>
                    ) : null
                }
            />

            <FlashBanner />

            <div className="flex-1 overflow-y-auto p-3.5">
                <div className="mb-3.5 rounded-lg border border-line bg-white p-3.5">
                    <div className="mb-2 flex justify-between gap-3">
                        <div className="min-w-0">
                            <h2 className="font-display text-lg leading-tight">{product.name}</h2>
                            <p className="mt-0.5 text-[11px] text-faint">{product.category}</p>
                        </div>

                        <div className="shrink-0 text-right">
                            {product.oldPrice ? (
                                <span className="block text-[10px] text-faint line-through">
                                    {money(product.oldPrice)}
                                </span>
                            ) : null}
                            <span className="text-xl font-bold text-brand">{money(product.price)}</span>
                        </div>
                    </div>

                    <Link href="/ui/reviews" className="mb-3 flex items-center gap-[5px]">
                        <Rating score={Math.round(Number(product.rating))} />
                        <span className="text-xs text-muted">
                            {product.rating} ({product.sold} terjual)
                        </span>
                    </Link>

                    <p className="text-xs leading-relaxed text-muted">{product.blurb}</p>
                </div>

                <div className="mb-3.5 rounded-lg border border-line bg-white p-3.5">
                    <div className="mb-[7px] text-[13px] font-bold">Kemasan</div>

                    <div className="flex flex-wrap gap-[7px]">
                        {product.variants.map((option) => (
                            <button
                                key={option}
                                type="button"
                                onClick={() => setVariant(option)}
                                className={`flex h-9 items-center justify-center px-3 text-[11px] ${
                                    variant === option
                                        ? 'border-2 border-brand font-bold text-brand'
                                        : 'border border-line text-muted'
                                }`}
                            >
                                {option}
                            </button>
                        ))}
                    </div>
                </div>

                <DrugInfoSection product={product} />

                <BranchPicker
                    productId={product.id}
                    productName={product.name}
                    maxQtyPerOrder={product.maxQtyPerOrder}
                />

                <div className="mb-2.5 mt-4 flex justify-between border-t border-line pt-2.5">
                    <span className="font-display text-sm">Ulasan</span>
                    <Link href="/ui/reviews" className="text-xs text-brand">
                        Lihat semua
                    </Link>
                </div>

                <ReviewCard review={reviews[0]} />
            </div>
        </MobileLayout>
    );
}
