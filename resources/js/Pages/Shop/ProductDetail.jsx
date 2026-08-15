import { useState } from 'react';
import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import Button from '@/Components/Shop/Button';
import Icon from '@/Components/Shop/Icon';
import Rating from '@/Components/Shop/Rating';
import ReviewCard from '@/Components/Shop/ReviewCard';
import { findProduct, money, reviews } from '@/Components/Shop/data';

/** The catalogue entry this screen shows; the prototype has no routing param. */
const product = findProduct('PRD-001');

export default function ProductDetail() {
    const [variant, setVariant] = useState(product.variants[0]);
    const [liked, setLiked] = useState(false);

    return (
        <MobileLayout title="Detail Produk">
            <div className="relative h-[285px] shrink-0 bg-[#f0f0f0]">
                <img
                    src={product.image}
                    alt={product.name}
                    className="absolute inset-0 h-full w-full object-contain p-8"
                />

                <Link
                    href="/ui/shop"
                    aria-label="Kembali"
                    className="absolute left-3 top-2.5 flex h-9 w-9 items-center justify-center bg-white/90"
                >
                    <Icon name="back" size={19} />
                </Link>

                <button
                    type="button"
                    onClick={() => setLiked((current) => ! current)}
                    aria-label={liked ? 'Hapus dari favorit' : 'Tambahkan ke favorit'}
                    className={`absolute right-3 top-2.5 flex h-9 w-9 items-center justify-center bg-white/90 ${
                        liked ? 'text-brand' : 'text-ink'
                    }`}
                >
                    <Icon name="heart" size={19} />
                </button>

                {product.prescription ? (
                    <span className="absolute bottom-3 left-3 bg-warning px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-ink">
                        Perlu resep
                    </span>
                ) : null}
            </div>

            <div className="flex-1 overflow-y-auto p-3.5">
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

                <p className="mb-3.5 text-xs leading-relaxed text-muted">{product.blurb}</p>

                <div className="mb-3.5">
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

                <div className="mb-3.5 flex items-center gap-2 border border-line bg-lilac px-3 py-2.5">
                    <Icon
                        name="check"
                        size={16}
                        className={product.stock > 0 ? 'text-success-deep' : 'text-brand'}
                    />
                    <span className="text-[11px] text-muted">
                        {product.stock > 0
                            ? `Stok tersedia — ${product.stock} ${product.unit.toLowerCase()}`
                            : 'Stok sedang kosong'}
                    </span>
                </div>

                <Button href="/ui/cart" className="mb-2">
                    Masukkan ke Keranjang
                </Button>

                <div className="mb-2.5 mt-4 flex justify-between border-t-2 border-ink pt-2.5">
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
