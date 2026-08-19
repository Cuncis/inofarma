import { useEffect, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import FlashBanner from '@/Components/Shop/FlashBanner';
import Icon from '@/Components/Shop/Icon';
import { money } from '@/Components/Shop/data';

/**
 * @param {{
 *   cart: { branch: object, address: object|null, items: object[], subtotal: number, coupon: object|null, discount: number },
 *   pickupEtaOptions: string[],
 * }} props
 */
export default function Checkout({ cart, pickupEtaOptions }) {
    const { branch } = cart;

    const [fulfilment, setFulfilment] = useState(
        branch.supportsDelivery ? 'antar' : 'ambil',
    );

    const { data, setData, post, processing, errors } = useForm({
        fulfilment,
        paymentMethod: 'online',
        pickupEta: pickupEtaOptions[0],
        courier: null,
        note: '',
    });

    const chooseFulfilment = (next) => {
        setFulfilment(next);
        setData((current) => ({
            ...current,
            fulfilment: next,
            // Bayar di tempat only makes sense for a pickup order.
            paymentMethod: next === 'antar' ? 'online' : current.paymentMethod,
        }));
    };

    // Live courier quotes (Fase 7) — refetched whenever the branch or
    // delivery address changes, never a static number, since `origin` is
    // this specific branch's own coordinates.
    const [courierOptions, setCourierOptions] = useState([]);
    const [courierLoading, setCourierLoading] = useState(false);

    useEffect(() => {
        if (fulfilment !== 'antar' || ! cart.address) {
            return;
        }

        let cancelled = false;
        setCourierLoading(true);

        fetch('/ui/checkout/ongkir', { headers: { Accept: 'application/json' } })
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

    const submit = (event) => {
        event.preventDefault();

        post('/ui/checkout', { preserveScroll: true });
    };

    return (
        <MobileLayout
            title="Checkout"
            header={<AppBar title="Checkout" back="/ui/cart" />}
        >
            <FlashBanner />

            <form onSubmit={submit} className="flex-1 overflow-y-auto p-3.5">
                <div className="mb-2 border border-line bg-lilac p-3.5">
                    <div className="mb-2.5 flex justify-between border-b-2 border-ink pb-2 font-display text-sm">
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
                            href="/ui/shipping-details"
                            className="mb-2 block border border-line bg-lilac p-3.5"
                        >
                            <div className="mb-2 flex items-center justify-between border-b-2 border-ink pb-2 font-display text-[13px]">
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
                                <div className="mb-2 border-b-2 border-ink pb-2 font-display text-[13px]">
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
                        <div className="mb-2 flex items-center justify-between border-b-2 border-ink pb-2 font-display text-[13px]">
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
                    <div className="mb-2 border-b-2 border-ink pb-2 font-display text-[13px]">
                        Metode pembayaran
                    </div>

                    {fulfilment === 'ambil' ? (
                        <div className="flex flex-wrap gap-[7px]">
                            <button
                                type="button"
                                onClick={() => setData('paymentMethod', 'online')}
                                className={`h-8 px-3 text-[11px] ${
                                    data.paymentMethod === 'online'
                                        ? 'border-2 border-brand font-bold text-brand'
                                        : 'border border-line text-muted'
                                }`}
                            >
                                Bayar Sekarang
                            </button>
                            <button
                                type="button"
                                onClick={() => setData('paymentMethod', 'Tunai')}
                                className={`h-8 px-3 text-[11px] ${
                                    data.paymentMethod === 'Tunai'
                                        ? 'border-2 border-brand font-bold text-brand'
                                        : 'border border-line text-muted'
                                }`}
                            >
                                Bayar di Tempat
                            </button>
                        </div>
                    ) : (
                        <p className="text-xs text-muted">
                            Anda akan diarahkan ke halaman pembayaran DOKU — pilih transfer
                            bank, e-wallet, QRIS atau kartu di sana.
                        </p>
                    )}

                    {errors.paymentMethod ? (
                        <p className="mt-1.5 text-[11px] text-danger">{errors.paymentMethod}</p>
                    ) : null}
                </div>

                <textarea
                    value={data.note}
                    onChange={(event) => setData('note', event.target.value)}
                    placeholder="Catatan (opsional)..."
                    rows={3}
                    className="mb-3.5 w-full resize-none border border-blush p-3 text-xs text-muted placeholder:text-[#bbbbbb] focus:outline-none focus:ring-0"
                />

                {errors.quantity ? (
                    <p className="mb-2 text-[11px] text-danger">{errors.quantity}</p>
                ) : null}

                <Button
                    type="submit"
                    disabled={processing || (fulfilment === 'antar' && (! cart.address || ! data.courier))}
                    className="mb-2"
                >
                    {processing
                        ? 'Memproses pesanan…'
                        : data.paymentMethod === 'online'
                          ? `Lanjut ke Pembayaran (${money(total)})`
                          : `Konfirmasi Pesanan (${money(total)})`}
                </Button>
            </form>
        </MobileLayout>
    );
}
