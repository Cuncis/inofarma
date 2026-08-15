import { useState } from 'react';
import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import Button from '@/Components/BeShop/Button';
import Icon from '@/Components/BeShop/Icon';
import Rating from '@/Components/BeShop/Rating';
import ReviewCard from '@/Components/BeShop/ReviewCard';
import { asset, money, reviews, sizes } from '@/Components/BeShop/data';

const colors = ['#222222', '#c4a882', '#0900AA', '#7ecac5'];

export default function ProductDetail() {
    const [color, setColor] = useState(colors[0]);
    const [size, setSize] = useState('S');
    const [liked, setLiked] = useState(false);

    return (
        <MobileLayout title="Detail Produk">
            <div className="relative h-[285px] shrink-0 bg-[#f0f0f0]">
                <img
                    src={asset.product('01')}
                    alt="Dress Maxi Sutra"
                    className="absolute inset-0 h-full w-full object-cover"
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
            </div>

            <div className="flex-1 overflow-y-auto p-3.5">
                <div className="mb-2 flex justify-between">
                    <h2 className="max-w-[55%] font-display text-lg leading-tight">
                        Dress Maxi Sutra
                    </h2>

                    <div className="text-right">
                        <span className="block text-[10px] text-faint line-through">
                            {money(1485000)}
                        </span>
                        <span className="text-xl font-bold text-brand">{money(1350000)}</span>
                    </div>
                </div>

                <Link
                    href="/ui/reviews"
                    className="mb-3.5 flex items-center gap-[5px]"
                >
                    <Rating score={5} />
                    <span className="text-xs text-muted">4.9 (128)</span>
                </Link>

                <div className="mb-3">
                    <div className="mb-[7px] text-[13px] font-bold">Warna</div>

                    <div className="flex gap-2">
                        {colors.map((swatch) => (
                            <button
                                key={swatch}
                                type="button"
                                onClick={() => setColor(swatch)}
                                aria-label={`Pilih warna ${swatch}`}
                                style={{ background: swatch }}
                                className={`h-[27px] w-[27px] rounded-full ${
                                    color === swatch
                                        ? 'outline outline-[2.5px] outline-offset-2 outline-brand'
                                        : ''
                                }`}
                            />
                        ))}
                    </div>
                </div>

                <div className="mb-3.5">
                    <div className="mb-[7px] text-[13px] font-bold">Ukuran</div>

                    <div className="flex gap-[7px]">
                        {sizes.map((option) => (
                            <button
                                key={option}
                                type="button"
                                onClick={() => setSize(option)}
                                className={`flex h-8 w-8 items-center justify-center text-[11px] ${
                                    size === option
                                        ? 'border-2 border-brand font-bold'
                                        : 'border border-line'
                                }`}
                            >
                                {option}
                            </button>
                        ))}
                    </div>
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
