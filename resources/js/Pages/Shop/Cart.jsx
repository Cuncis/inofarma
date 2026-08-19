import { useEffect, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import FlashBanner from '@/Components/Shop/FlashBanner';
import Icon from '@/Components/Shop/Icon';
import IconLink from '@/Components/Shop/IconLink';
import TabBar from '@/Components/Shop/TabBar';
import useShopUser from '@/Components/Shop/useShopUser';
import { money } from '@/Components/Shop/data';

/**
 * @param {{ cart: {
 *   branch: object|null, address: object|null, items: object[], itemCount: number,
 *   subtotal: number, coupon: object|null, discount: number,
 * } }} props
 */
export default function Cart({ cart }) {
    const { signedIn } = useShopUser();
    const [promo, setPromo] = useState('');
    const [busySku, setBusySku] = useState(null);
    const [couponError, setCouponError] = useState('');

    useEffect(() => {
        if (cart.itemCount === 0) {
            router.visit('/ui/cart-empty');
        }
    }, [cart.itemCount]);

    const changeQuantity = (item, quantity) => {
        setBusySku(item.sku);

        router.patch(
            `/ui/keranjang/${item.sku}`,
            { quantity },
            { preserveScroll: true, onFinish: () => setBusySku(null) },
        );
    };

    const removeItem = (item) => {
        setBusySku(item.sku);

        router.delete(`/ui/keranjang/${item.sku}`, {
            preserveScroll: true,
            onFinish: () => setBusySku(null),
        });
    };

    const applyPromo = () => {
        if (! promo.trim()) {
            return;
        }

        setCouponError('');

        router.post(
            '/ui/keranjang/kupon',
            { code: promo.trim() },
            {
                preserveScroll: true,
                onSuccess: () => setPromo(''),
                onError: (errors) => setCouponError(errors.code ?? ''),
            },
        );
    };

    const removePromo = () => {
        router.delete('/ui/keranjang/kupon', { preserveScroll: true });
    };

    const total = Math.max(cart.subtotal - cart.discount, 0);

    return (
        <MobileLayout
            title="Keranjang"
            header={
                <AppBar
                    title="Pesanan"
                    tone="brand"
                    actions={
                        <IconLink name="history" href="/ui/order-history" label="Riwayat transaksi" />
                    }
                />
            }
            footer={<TabBar active="order" />}
        >
            <FlashBanner />

            <div className="flex-1 overflow-y-auto px-3.5 pt-3.5">
                {cart.branch ? (
                    <div className="mb-3 border border-line bg-lilac p-2.5 text-[11px]">
                        <div className="flex items-center gap-2">
                            <Icon name="pin" size={14} className="shrink-0 text-brand" />
                            <span>
                                Belanja dari <strong>{cart.branch.name}</strong> ({cart.branch.kota})
                            </span>
                        </div>

                        {cart.branch.apjName && cart.branch.apjWhatsappUrl ? (
                            <a
                                href={cart.branch.apjWhatsappUrl}
                                target="_blank"
                                rel="noreferrer"
                                className="mt-1.5 flex items-center gap-2 text-brand"
                            >
                                <Icon name="phone" size={14} className="shrink-0" />
                                Tanya Apoteker {cart.branch.apjName} di WhatsApp
                            </a>
                        ) : null}
                    </div>
                ) : null}

                {cart.items.map((item) => (
                    <div
                        key={item.sku}
                        className="mb-2 flex min-h-[88px] gap-2.5 border border-line p-2.5"
                    >
                        <Link
                            href={`/ui/product-detail?id=${item.sku}`}
                            className="relative w-[68px] shrink-0"
                        >
                            <img
                                src={item.image}
                                alt={item.name}
                                className="h-full w-[68px] object-cover"
                            />
                        </Link>

                        <Link
                            href={`/ui/product-detail?id=${item.sku}`}
                            className="flex flex-1 flex-col justify-center"
                        >
                            <div className="mb-1 text-[13px] font-semibold text-ink">
                                {item.name}
                            </div>
                            <div className="text-xs font-bold text-muted">
                                {money(item.unitPrice)}
                            </div>
                            {item.quantity > item.available ? (
                                <div className="mt-1 text-[10px] font-semibold text-danger">
                                    Stok tersisa {item.available}
                                </div>
                            ) : null}
                        </Link>

                        <div className="flex min-w-[24px] flex-col items-center justify-between py-1">
                            <button
                                type="button"
                                disabled={busySku === item.sku}
                                onClick={() => changeQuantity(item, item.quantity + 1)}
                                aria-label={`Tambah jumlah ${item.name}`}
                                className="flex h-[22px] w-[22px] items-center justify-center border border-line text-sm leading-none disabled:opacity-40"
                            >
                                +
                            </button>

                            <span className="text-xs">{item.quantity}</span>

                            <button
                                type="button"
                                disabled={busySku === item.sku}
                                onClick={() =>
                                    item.quantity <= 1
                                        ? removeItem(item)
                                        : changeQuantity(item, item.quantity - 1)
                                }
                                aria-label={`Kurangi jumlah ${item.name}`}
                                className="flex h-[22px] w-[22px] items-center justify-center border border-line text-sm leading-none disabled:opacity-40"
                            >
                                −
                            </button>
                        </div>
                    </div>
                ))}

                {signedIn ? (
                    <div className="mb-[18px]">
                        {cart.coupon ? (
                            <div className="flex h-[50px] items-center justify-between border border-brand bg-brand/5 px-3.5 text-xs">
                                <span className="font-bold text-brand">{cart.coupon.code}</span>
                                <button
                                    type="button"
                                    onClick={removePromo}
                                    className="text-[11px] font-bold uppercase text-muted"
                                >
                                    Hapus
                                </button>
                            </div>
                        ) : (
                            <div className="grid grid-cols-[1.5fr_1fr] gap-2">
                                <input
                                    value={promo}
                                    onChange={(event) => setPromo(event.target.value)}
                                    placeholder="Masukkan kode promo"
                                    className="h-[50px] border border-blush px-3.5 text-xs text-muted placeholder:text-[#bbbbbb] focus:outline-none focus:ring-0"
                                />

                                <button
                                    type="button"
                                    onClick={applyPromo}
                                    className="flex h-[50px] items-center justify-center border border-line bg-lilac text-xs font-bold uppercase"
                                >
                                    Pakai
                                </button>
                            </div>
                        )}

                        {couponError ? (
                            <p className="mt-1 text-[11px] text-danger">{couponError}</p>
                        ) : null}
                    </div>
                ) : (
                    <p className="mb-[18px] text-[11px] text-muted">
                        <Link href="/ui/signin" className="text-brand">
                            Masuk
                        </Link>{' '}
                        untuk memakai kode promo.
                    </p>
                )}

                <div className="mb-3.5 border border-line bg-lilac p-3.5">
                    <div className="mb-1.5 flex justify-between text-[13px]">
                        <span>Subtotal</span>
                        <span className="font-bold">{money(cart.subtotal)}</span>
                    </div>

                    {cart.discount > 0 ? (
                        <div className="mb-1.5 flex justify-between text-[13px]">
                            <span>Diskon</span>
                            <span className="text-brand">-{money(cart.discount)}</span>
                        </div>
                    ) : null}

                    <div className="mb-1.5 flex justify-between border-b-2 border-ink pb-1.5 text-[13px] text-muted">
                        <span>Ongkir &amp; pajak</span>
                        <span>Dihitung saat checkout</span>
                    </div>

                    <div className="flex justify-between text-[13px]">
                        <span className="font-bold">Total</span>
                        <span className="font-bold">{money(total)}</span>
                    </div>
                </div>

                <Button href="/ui/checkout" className="mb-2">
                    Lanjut ke Pembayaran
                </Button>
            </div>
        </MobileLayout>
    );
}
