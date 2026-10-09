import { useEffect, useState, type FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import DesktopCheckoutShell, { CheckoutSummary } from '@/Components/Shop/DesktopCheckoutShell';
import FlashBanner from '@/Components/Shop/FlashBanner';
import Icon from '@/Components/Shop/Icon';
import useIsDesktop from '@/Components/Shop/useIsDesktop';
import useShopUser from '@/Components/Shop/useShopUser';
import { money } from '@/Components/Shop/data';

export default function Checkout({ cart, pickupEtaOptions }: {
  cart: { branch: CartBranch, address: SavedAddress | null, items: CartPreviewItem[], subtotal: number, coupon: CartCoupon | null, discount: number },
  pickupEtaOptions: string[],
}) {
    const { branch } = cart;
    const isDesktop = useIsDesktop();
    const { name: customerName, email: customerEmail } = useShopUser();

    const [fulfilment, setFulfilment] = useState<'antar' | 'ambil'>(
        branch.supportsDelivery ? 'antar' : 'ambil',
    );

    const { data, setData, post, processing, errors: formErrors } = useForm({
        fulfilment,
        paymentMethod: 'online',
        pickupEta: pickupEtaOptions[0],
        courier: null as CourierOption | null,
        note: '',
    });

    const errors = formErrors as typeof formErrors & { address?: string; quantity?: string };

    const chooseFulfilment = (next: 'antar' | 'ambil') => {
        setFulfilment(next);
        setData((current) => ({
            ...current,
            fulfilment: next,
        }));
    };

    // Live courier quotes (Fase 7) — refetched whenever the branch or
    // delivery address changes, never a static number, since `origin` is
    // this specific branch's own coordinates.
    const [courierOptions, setCourierOptions] = useState<CourierOption[]>([]);
    const [courierLoading, setCourierLoading] = useState(false);

    useEffect(() => {
        if (fulfilment !== 'antar' || ! cart.address) {
            return;
        }

        let cancelled = false;
        setCourierLoading(true);

        fetch('/checkout/ongkir', { headers: { Accept: 'application/json' } })
            .then((response) => response.json())
            .then((body) => {
                if (cancelled) {
                    return;
                }

                const options = body.options ?? [];
                setCourierOptions(options);
                setData('courier', options[0] ?? null);
            })
            .finally(() => {
                if (! cancelled) {
                    setCourierLoading(false);
                }
            });

        return () => {
            cancelled = true;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [fulfilment, cart.address?.id, branch.id]);

    const freeShipping = Boolean(cart.coupon?.freeShipping);
    const shippingQuote = fulfilment === 'antar' ? data.courier?.price ?? 0 : 0;
    const shipping = freeShipping ? 0 : shippingQuote;
    const total = Math.max(cart.subtotal - cart.discount, 0) + shipping;

    const lines = cart.items.map((item) => ({
        label: `${item.name} x${item.quantity}`,
        value: money(item.lineTotal),
    }));

    const submit = (event: FormEvent) => {
        event.preventDefault();

        post('/checkout', { preserveScroll: true });
    };

    if (isDesktop) {
        const shippingLabel = fulfilment === 'ambil' || freeShipping
            ? 'Gratis'
            : data.courier ? money(shipping) : 'Pilih alamat dan kurir';

        const choice = (active: boolean) => `flex h-[58px] flex-1 items-center justify-center gap-2 rounded-[2px] text-[14px] font-bold ${
            active ? 'bg-white shadow-card ring-1 ring-black/10' : 'text-ink'
        }`;
        const option = (active: boolean) => `flex w-full items-center justify-between border px-4 py-3.5 text-left text-[13px] ${
            active ? 'border-2 border-brand' : 'border-line'
        }`;

        return (
            <DesktopCheckoutShell
                title="Checkout"
                summary={
                    <CheckoutSummary
                        items={cart.items}
                        subtotal={cart.subtotal}
                        discount={cart.discount}
                        couponCode={cart.coupon?.code}
                        shippingLabel={shippingLabel}
                        total={total}
                    />
                }
            >
                <FlashBanner />

                <form onSubmit={submit}>
                    <h2 className="mb-4 font-display text-[22px]">Kontak</h2>
                    <div className="mb-8 border border-line px-4 py-3.5 text-[13px]">
                        <div className="font-bold">{customerName}</div>
                        <div className="text-muted">{customerEmail}</div>
                    </div>

                    <h2 className="mb-4 font-display text-[22px]">Pengantaran</h2>
                    <div className="mb-4 flex gap-1 rounded-[2px] bg-[#f5f5f5] p-1">
                        {branch.supportsDelivery ? (
                            <button type="button" onClick={() => chooseFulfilment('antar')} className={choice(fulfilment === 'antar')}>
                                <Icon name="bagSimple" size={18} />
                                Kirim ke alamat
                            </button>
                        ) : null}
                        {branch.supportsPickup ? (
                            <button type="button" onClick={() => chooseFulfilment('ambil')} className={choice(fulfilment === 'ambil')}>
                                <Icon name="pin" size={18} />
                                Ambil di toko
                            </button>
                        ) : null}
                    </div>
                    {errors.fulfilment ? <p className="mb-3 text-[12px] text-danger">{errors.fulfilment}</p> : null}

                    {fulfilment === 'antar' ? (
                        <>
                            <Link href="/shipping-details" className="mb-6 block border border-line px-4 py-3.5">
                                <div className="mb-1 flex items-center justify-between text-[13px] font-bold">
                                    <span>Alamat pengiriman</span>
                                    <Icon name="edit" size={16} />
                                </div>
                                {cart.address ? (
                                    <span className="text-[13px] text-muted">{cart.address.fullAddress}</span>
                                ) : (
                                    <span className="text-[13px] text-link">Pilih alamat pengiriman &rarr;</span>
                                )}
                            </Link>

                            {cart.address ? (
                                <>
                                    <h2 className="mb-3 font-display text-[18px]">Metode pengiriman</h2>
                                    {courierLoading ? (
                                        <p className="mb-6 text-[13px] text-muted">Memuat pilihan kurir…</p>
                                    ) : courierOptions.length === 0 ? (
                                        <p className="mb-6 text-[13px] text-danger">
                                            Tidak ada kurir yang menjangkau alamat ini. Coba alamat lain.
                                        </p>
                                    ) : (
                                        <div className="mb-6 space-y-2">
                                            {courierOptions.map((courier) => {
                                                const selected = Boolean(
                                                    data.courier
                                                    && data.courier.courierCompany === courier.courierCompany
                                                    && data.courier.courierType === courier.courierType,
                                                );

                                                return (
                                                    <button
                                                        key={`${courier.courierCompany}-${courier.courierType}`}
                                                        type="button"
                                                        onClick={() => setData('courier', courier)}
                                                        className={option(selected)}
                                                    >
                                                        <span>
                                                            {courier.courierName} {courier.serviceName}
                                                            {courier.duration ? (
                                                                <span className="block text-[12px] text-muted">{courier.duration}</span>
                                                            ) : null}
                                                        </span>
                                                        <span>{money(courier.price)}</span>
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    )}
                                </>
                            ) : null}
                        </>
                    ) : (
                        <div className="mb-6 border border-line px-4 py-3.5">
                            <div className="mb-1 text-[13px] font-bold">Ambil di {branch.name}</div>
                            <p className="mb-3 text-[13px] text-muted">{branch.fullAddress}</p>
                            <div className="flex flex-wrap gap-2">
                                {pickupEtaOptions.map((eta) => (
                                    <button
                                        key={eta}
                                        type="button"
                                        onClick={() => setData('pickupEta', eta)}
                                        className={`h-9 px-3.5 text-[12px] ${
                                            data.pickupEta === eta ? 'border-2 border-brand font-bold text-brand' : 'border border-line text-muted'
                                        }`}
                                    >
                                        {eta}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}
                    {errors.address ? <p className="mb-3 text-[12px] text-danger">{errors.address}</p> : null}
                    {errors.courier ? <p className="mb-3 text-[12px] text-danger">{errors.courier}</p> : null}

                    <h2 className="mb-3 font-display text-[18px]">Metode pembayaran</h2>
                    <div className="mb-6 border-2 border-brand px-4 py-3.5 text-[13px]">
                        <div className="font-bold text-brand">Bayar Online (DOKU)</div>
                        <p className="mt-1 text-muted">
                            Anda akan diarahkan ke halaman pembayaran DOKU: transfer bank, e-wallet, atau QRIS.
                        </p>
                    </div>
                    {errors.paymentMethod ? <p className="mb-3 text-[12px] text-danger">{errors.paymentMethod}</p> : null}

                    <textarea
                        value={data.note}
                        onChange={(event) => setData('note', event.target.value)}
                        placeholder="Catatan (opsional)..."
                        rows={3}
                        className="mb-6 w-full resize-none border border-line p-3 text-[13px] text-muted placeholder:text-[#bbbbbb] focus:outline-hidden focus:ring-0"
                    />
                    {errors.quantity ? <p className="mb-3 text-[12px] text-danger">{errors.quantity}</p> : null}

                    <div className="flex items-center justify-between">
                        <Link href="/cart" className="text-[13px] text-link">&lsaquo; Kembali ke keranjang</Link>
                        <button
                            type="submit"
                            disabled={processing || (fulfilment === 'antar' && (! cart.address || ! data.courier))}
                            className="h-[52px] bg-success px-8 text-[14px] font-bold text-white disabled:opacity-60"
                        >
                            {processing ? 'Memproses pesanan…' : 'Lanjut ke Pembayaran'}
                        </button>
                    </div>
                </form>
            </DesktopCheckoutShell>
        );
    }

    return (
        <MobileLayout
            title="Checkout"
            header={<AppBar title="Checkout" back="/cart" tone="brand" />}
        >
            <FlashBanner />

            <form onSubmit={submit} className="flex-1 overflow-y-auto p-3.5">
                <div className="mb-2 border border-line bg-lilac p-3.5">
                    <div className="mb-2.5 flex justify-between border-b border-line pb-2 font-display text-sm">
                        <span>Pesanan saya</span>
                        <span>{money(total)}</span>
                    </div>

                    {lines.map((line) => (
                        <div
                            key={line.label}
                            className="mb-[5px] flex justify-between text-xs text-muted"
                        >
                            <span>{line.label}</span>
                            <span>{line.value}</span>
                        </div>
                    ))}

                    {cart.discount > 0 ? (
                        <div className="mb-[5px] flex justify-between text-xs text-brand">
                            <span>Diskon ({cart.coupon?.code})</span>
                            <span>-{money(cart.discount)}</span>
                        </div>
                    ) : null}

                    <div className="flex justify-between text-xs text-muted">
                        <span>Pengiriman</span>
                        <span className={shipping === 0 ? 'text-success-deep' : ''}>
                            {fulfilment === 'ambil' || freeShipping
                                ? 'Gratis'
                                : data.courier
                                  ? money(shipping)
                                  : '—'}
                        </span>
                    </div>
                </div>

                <div className="mb-2 flex gap-[7px]">
                    {branch.supportsDelivery ? (
                        <button
                            type="button"
                            onClick={() => chooseFulfilment('antar')}
                            className={`flex flex-1 items-center justify-center gap-1.5 py-2.5 text-[11px] ${
                                fulfilment === 'antar'
                                    ? 'border-2 border-brand font-bold text-brand'
                                    : 'border border-line text-muted'
                            }`}
                        >
                            <Icon name="bagSimple" size={14} />
                            Antar
                        </button>
                    ) : null}

                    {branch.supportsPickup ? (
                        <button
                            type="button"
                            onClick={() => chooseFulfilment('ambil')}
                            className={`flex flex-1 items-center justify-center gap-1.5 py-2.5 text-[11px] ${
                                fulfilment === 'ambil'
                                    ? 'border-2 border-brand font-bold text-brand'
                                    : 'border border-line text-muted'
                            }`}
                        >
                            <Icon name="pin" size={14} />
                            Ambil di Tempat
                        </button>
                    ) : null}
                </div>
                {errors.fulfilment ? (
                    <p className="mb-2 text-[11px] text-danger">{errors.fulfilment}</p>
                ) : null}

                {fulfilment === 'antar' ? (
                    <>
                        <Link
                            href="/shipping-details"
                            className="mb-2 block border border-line bg-lilac p-3.5"
                        >
                            <div className="mb-2 flex items-center justify-between border-b border-line pb-2 font-display text-[13px]">
                                <span>Detail pengiriman</span>
                                <Icon name="edit" size={15} className="text-ink" />
                            </div>

                            {cart.address ? (
                                <span className="text-xs text-muted">{cart.address.fullAddress}</span>
                            ) : (
                                <span className="text-xs text-brand">Pilih alamat pengiriman →</span>
                            )}
                        </Link>

                        {cart.address ? (
                            <div className="mb-2 border border-line bg-lilac p-3.5">
                                <div className="mb-2 border-b border-line pb-2 font-display text-[13px]">
                                    Pilih kurir
                                </div>

                                {courierLoading ? (
                                    <p className="text-xs text-muted">Memuat pilihan kurir…</p>
                                ) : courierOptions.length === 0 ? (
                                    <p className="text-xs text-danger">
                                        Tidak ada kurir yang menjangkau alamat ini. Coba alamat lain.
                                    </p>
                                ) : (
                                    <div className="space-y-1.5">
                                        {courierOptions.map((option) => {
                                            const selected = data.courier
                                                && data.courier.courierCompany === option.courierCompany
                                                && data.courier.courierType === option.courierType;

                                            return (
                                                <button
                                                    key={`${option.courierCompany}-${option.courierType}`}
                                                    type="button"
                                                    onClick={() => setData('courier', option)}
                                                    className={`flex w-full items-center justify-between px-3 py-2 text-left text-[11px] ${
                                                        selected
                                                            ? 'border-2 border-brand font-bold text-brand'
                                                            : 'border border-line text-muted'
                                                    }`}
                                                >
                                                    <span>
                                                        {option.courierName} {option.serviceName}
                                                        {option.duration ? (
                                                            <span className="block text-[10px] font-normal text-muted">
                                                                {option.duration}
                                                            </span>
                                                        ) : null}
                                                    </span>
                                                    <span>{money(option.price)}</span>
                                                </button>
                                            );
                                        })}
                                    </div>
                                )}
                            </div>
                        ) : null}
                    </>
                ) : (
                    <div className="mb-2 border border-line bg-lilac p-3.5">
                        <div className="mb-2 flex items-center justify-between border-b border-line pb-2 font-display text-[13px]">
                            <span>Ambil di {branch.name}</span>
                        </div>

                        <p className="mb-2 text-xs text-muted">{branch.fullAddress}</p>

                        <div className="flex flex-wrap gap-[7px]">
                            {pickupEtaOptions.map((option) => (
                                <button
                                    key={option}
                                    type="button"
                                    onClick={() => setData('pickupEta', option)}
                                    className={`h-8 px-3 text-[11px] ${
                                        data.pickupEta === option
                                            ? 'border-2 border-brand font-bold text-brand'
                                            : 'border border-line text-muted'
                                    }`}
                                >
                                    {option}
                                </button>
                            ))}
                        </div>
                    </div>
                )}
                {errors.address ? (
                    <p className="mb-2 text-[11px] text-danger">{errors.address}</p>
                ) : null}
                {errors.courier ? (
                    <p className="mb-2 text-[11px] text-danger">{errors.courier}</p>
                ) : null}

                <div className="mb-2 border border-line bg-lilac p-3.5">
                    <div className="mb-2 border-b border-line pb-2 font-display text-[13px]">
                        Metode pembayaran
                    </div>

                    <div className="flex flex-wrap gap-[7px]">
                        <span className="flex h-8 items-center gap-1.5 border-2 border-brand px-3 text-[11px] font-bold text-brand">
                            <Icon name="check" size={12} />
                            Bayar Online (DOKU)
                        </span>
                    </div>

                    <p className="mt-1.5 text-xs text-muted">
                        Anda akan diarahkan ke halaman pembayaran DOKU, pilih transfer bank,
                        e-wallet, atau QRIS di sana.
                    </p>

                    {errors.paymentMethod ? (
                        <p className="mt-1.5 text-[11px] text-danger">{errors.paymentMethod}</p>
                    ) : null}
                </div>

                <textarea
                    value={data.note}
                    onChange={(event) => setData('note', event.target.value)}
                    placeholder="Catatan (opsional)..."
                    rows={3}
                    className="mb-3.5 w-full resize-none border border-blush p-3 text-xs text-muted placeholder:text-[#bbbbbb] focus:outline-hidden focus:ring-0"
                />

                {errors.quantity ? (
                    <p className="mb-2 text-[11px] text-danger">{errors.quantity}</p>
                ) : null}

                <Button
                    type="submit"
                    disabled={processing || (fulfilment === 'antar' && (! cart.address || ! data.courier))}
                    className="mb-2"
                >
                    {processing ? 'Memproses pesanan…' : `Lanjut ke Pembayaran (${money(total)})`}
                </Button>
            </form>
        </MobileLayout>
    );
}
