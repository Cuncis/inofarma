import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import Button from '@/Components/BeShop/Button';
import Icon from '@/Components/BeShop/Icon';
import Rating from '@/Components/BeShop/Rating';
import ReviewCard from '@/Components/BeShop/ReviewCard';
import { asset, reviews, sizes } from '@/Components/BeShop/data';

const colors = ['#222222', '#c4a882', '#d05278', '#7ecac5'];

export default function ProductDetail() {
    return (
        <MobileLayout title="Product Detail">
            <div className="relative h-[285px] shrink-0 bg-[#f0f0f0]">
                <img
                    src={asset.product('01')}
                    alt="Silk Maxi Dress"
                    className="absolute inset-0 h-full w-full object-cover"
                />

                <Link
                    href="/ui/shop"
                    aria-label="Go back"
                    className="absolute left-3 top-2.5 flex h-9 w-9 items-center justify-center bg-white/90"
                >
                    <Icon name="back" size={19} />
                </Link>

                <div className="absolute right-3 top-2.5 flex h-9 w-9 items-center justify-center bg-white/90">
                    <Icon name="heart" size={19} />
                </div>
            </div>

            <div className="flex-1 overflow-y-auto p-3.5">
                <div className="mb-2 flex justify-between">
                    <h2 className="max-w-[55%] font-display text-lg leading-tight">
                        Silk Maxi Dress
                    </h2>

                    <div className="text-right">
                        <span className="block text-[10px] text-faint line-through">
                            $99.00
                        </span>
                        <span className="text-xl font-bold text-brand">$89.99</span>
                    </div>
                </div>

                <div className="mb-3.5 flex items-center gap-[5px]">
                    <Rating score={5} />
                    <span className="text-xs text-muted">4.9 (128)</span>
                </div>

                <div className="mb-3">
                    <div className="mb-[7px] text-[13px] font-bold">Color</div>

                    <div className="flex gap-2">
                        {colors.map((color, index) => (
                            <div
                                key={color}
                                style={{ background: color }}
                                className={`h-[27px] w-[27px] rounded-full ${
                                    index === 0
                                        ? 'outline outline-[2.5px] outline-offset-2 outline-brand'
                                        : ''
                                }`}
                            />
                        ))}
                    </div>
                </div>

                <div className="mb-3.5">
                    <div className="mb-[7px] text-[13px] font-bold">Size</div>

                    <div className="flex gap-[7px]">
                        {sizes.map((size) => (
                            <div
                                key={size}
                                className={`flex h-8 w-8 items-center justify-center text-[11px] ${
                                    size === 'S'
                                        ? 'border-2 border-brand font-bold'
                                        : 'border border-line'
                                }`}
                            >
                                {size}
                            </div>
                        ))}
                    </div>
                </div>

                <Button className="mb-2">Add to Cart</Button>

                <div className="mb-2.5 mt-4 flex justify-between border-t-2 border-ink pt-2.5">
                    <span className="font-display text-sm">Reviews</span>
                    <Link href="/ui/reviews" className="text-xs text-brand">
                        View all
                    </Link>
                </div>

                <ReviewCard review={reviews[0]} />
            </div>
        </MobileLayout>
    );
}
