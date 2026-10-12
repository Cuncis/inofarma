import { useEffect, useRef, useState, type FormEvent } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AddressFields from '@/Components/Shop/AddressFields';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import DesktopCheckoutShell, { CheckoutSummary } from '@/Components/Shop/DesktopCheckoutShell';
import Field from '@/Components/Shop/Field';
import FlashBanner from '@/Components/Shop/FlashBanner';
import PickupLocationModal from '@/Components/Shop/PickupLocationModal';
import Icon from '@/Components/Shop/Icon';
import { pointForPostalCode, saveShopperLocation } from '@/Components/Shop/saveShopperLocation';
import useIsDesktop from '@/Components/Shop/useIsDesktop';
import useShopUser from '@/Components/Shop/useShopUser';
import { money } from '@/Components/Shop/data';

/**
 * The whole checkout on one page, for guests and customers alike: who you are,
 * where it goes, delivery or pickup, and payment. A guest's account is created
 * on submit (`CheckoutController::registerGuest()`), and a shopper without a
 * saved address types one here instead of leaving for another screen.
 */
export default function Checkout({ cart, pickupBranches, isGuest, provinces }: {
  cart: { branch: CartBranch, address: SavedAddress | null, items: CartPreviewItem[], subtotal: number, coupon: CartCoupon | null, discount: number },
  pickupBranches: ProductBranch[],
  isGuest: boolean,
  provinces: { code: string, name: string }[],
}) {
    const { branch } = cart;
    const isDesktop = useIsDesktop();
    const { name: customerName, email: customerEmail } = useShopUser();

    const [fulfilment, setFulfilment] = useState<'antar' | 'ambil'>(
        branch.supportsDelivery ? 'antar' : 'ambil',
    );
    const [locationOpen, setLocationOpen] = useState(false);
    const canPickup = pickupBranches.length > 0;

    const { data, setData, post, processing, errors: formErrors } = useForm({
        fulfilment,
        paymentMethod: 'online',
        // Pickup is chosen here: the cart's own branch if it can fill the order, else the first that can.
        pickupBranchId: (pickupBranches.find((candidate) => candidate.id === branch.id && candidate.selectable)
            ?? pickupBranches.find((candidate) => candidate.selectable))?.id ?? '',
        courier: null as CourierOption | null,
        note: '',
        name: '',
        phone: '',
        email: '',
        consent: false,
        addressLine: '',
        kelurahan: '',
        kecamatan: '',
        kota: '',
        provinsi: '',
        postalCode: '',
        latitude: null as number | null,
        longitude: null as number | null,
    });

    // No saved address: the shopper types one into this same form.
    const typingAddress = ! cart.address;

    const errors = formErrors as typeof formErrors & { address?: string; quantity?: string; pickupBranchId?: string };
    const pickedPickupByHand = useRef(false);
    const selectedPickup = pickupBranches.find((candidate) => candidate.id === data.pickupBranchId) ?? null;

    // After the shopper shares a location or a postal code, only the branch
    // list is fetched again, so distances appear without losing the form.
    const reloadPickupBranches = () => new Promise<void>((resolve) => {
        router.reload({ only: ['pickupBranches'], onFinish: () => resolve() });
    });

    // The moment the address reaches a postal code (or a map pin is set), work
    // out where the shopper is so every pickup point shows its distance.
    const postalReady = /^\d{5}$/.test(data.postalCode);
    const hasPin = data.latitude !== null && data.longitude !== null;
    const locationKey = typingAddress ? (hasPin ? `${data.latitude},${data.longitude}` : postalReady ? data.postalCode : '') : '';

    useEffect(() => {
        if (! locationKey) {
            return;
        }

        let cancelled = false;

        (async () => {
            try {
                const point = hasPin
                    ? { lat: Number(data.latitude), lng: Number(data.longitude) }
                    : await pointForPostalCode(data.postalCode);

                if (! point || cancelled) {
                    return;
                }

                await saveShopperLocation(point.lat, point.lng);

                if (! cancelled) {
                    await reloadPickupBranches();
                }
            } catch {
                // Distance is a nicety: without it the list simply shows no km.
            }
        })();

        return () => {
            cancelled = true;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [locationKey]);

    // Until the shopper picks a place themselves, the nearest one that has the
    // items stays selected as distances arrive.
    useEffect(() => {
        if (pickedPickupByHand.current) {
            return;
        }

        const nearest = pickupBranches.find((candidate) => candidate.selectable);

        if (nearest && nearest.id !== data.pickupBranchId) {
            setData('pickupBranchId', nearest.id);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [pickupBranches]);

    const pickupDetails = (
        <>
            <div className="flex items-baseline justify-between gap-3">
                <span className="text-[13px] font-bold">Ambil di {selectedPickup?.name ?? 'toko'}</span>
                <button type="button" onClick={() => setLocationOpen(true)} className="shrink-0 text-[12px] text-link underline">
                    Ubah lokasi
                </button>
            </div>
            <p className="mt-1 text-[13px] text-muted">{selectedPickup?.fullAddress}</p>
            <p className="mb-3 mt-1 text-[12px] text-muted">
                Biasanya siap dalam 1 jam
                {selectedPickup?.distanceKm != null ? ` · Estimasi jarak ${selectedPickup.distanceKm} km` : ''}
            </p>
            {typingAddress && selectedPickup?.distanceKm == null ? (
                <p className="mb-3 text-[12px] text-muted">Lengkapi alamat sampai kode pos untuk melihat estimasi jarak.</p>
            ) : null}
            {errors.pickupBranchId ? <p className="mb-2 text-[12px] text-danger">{errors.pickupBranchId}</p> : null}
        </>
    );

    const locationModal = locationOpen ? (
        <PickupLocationModal
            branches={pickupBranches}
            selectedId={data.pickupBranchId || null}
            onSave={(id) => {
                pickedPickupByHand.current = true;
                setData('pickupBranchId', id);
            }}
            onClose={() => setLocationOpen(false)}
            onLocated={reloadPickupBranches}
        />
    ) : null;

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
    // Why there are no options, when the server says so (missing map point, the
    // courier service failing), instead of always claiming no courier reaches.
    const [courierError, setCourierError] = useState<string | null>(null);

    useEffect(() => {
        if (fulfilment !== 'antar' || (! cart.address && ! postalReady && ! hasPin)) {
            return;
        }

        let cancelled = false;
        setCourierLoading(true);

        setCourierError(null);

        const query = cart.address ? '' : `?${new URLSearchParams({
            postalCode: data.postalCode,
            ...(hasPin ? { latitude: String(data.latitude), longitude: String(data.longitude) } : {}),
        })}`;

        fetch(`/checkout/ongkir${query}`, { headers: { Accept: 'application/json' } })
            .then((response) => response.json().catch(() => ({ error: 'Gagal memuat ongkos kirim. Coba lagi.' })))
            .then((body) => {
                if (cancelled) {
                    return;
                }

                const options = body.options ?? [];
                setCourierOptions(options);
                setCourierError(options.length === 0 ? (body.error ?? null) : null);
                setData('courier', options[0] ?? null);
            })
            .catch(() => {
                if (! cancelled) {
                    setCourierOptions([]);
                    setCourierError('Tidak bisa memuat ongkos kirim. Periksa koneksi Anda, lalu coba lagi.');
                    setData('courier', null);
                }
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
    }, [fulfilment, cart.address?.id, branch.id, data.postalCode, data.latitude, data.longitude]);

    const freeShipping = Boolean(cart.coupon?.freeShipping);
    const shippingQuote = fulfilment === 'antar' ? data.courier?.price ?? 0 : 0;
    const shipping = freeShipping ? 0 : shippingQuote;
    const total = Math.max(cart.subtotal - cart.discount, 0) + shipping;

    const lines = cart.items.map((item) => ({
        label: `${item.name} x${item.quantity}`,
        value: money(item.lineTotal),
    }));

    const addressReady = Boolean(cart.address) || postalReady || hasPin;
    const cannotSubmit = processing
        || (isGuest && ! data.consent)
        || (fulfilment === 'antar' && (! addressReady || ! data.courier));

    const submit = (event: FormEvent) => {
        event.preventDefault();

        post('/checkout', { preserveScroll: true });
    };

    const addressErrors = errors as Record<string, string | undefined>;

    const contactFields = isGuest ? (
        <>
            <Field
                type="email"
                name="email"
                label="Email"
                value={data.email}
                onChange={(event) => setData('email', event.target.value)}
                placeholder="Contoh: kirana.wijaya@mail.com"
                error={addressErrors.email}
                autoComplete="off"
                className="mb-2.5"
            />
            <div className="mb-2.5 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                <Field
                    name="name"
                    label="Nama Lengkap"
                    value={data.name}
                    onChange={(event) => setData('name', event.target.value)}
                    placeholder="Contoh: Kirana Wijaya"
                    error={addressErrors.name}
                    autoComplete="off"
                />
                <Field
                    type="tel"
                    name="phone"
                    label="Telepon"
                    value={data.phone}
                    onChange={(event) => setData('phone', event.target.value)}
                    placeholder="Contoh: 081234567890"
                    error={addressErrors.phone}
                    autoComplete="off"
                />
            </div>
        </>
    ) : null;

    const addressFields = typingAddress ? (
        <>
            <Field
                name="addressLine"
                label="Alamat Lengkap"
                value={data.addressLine}
                onChange={(event) => setData('addressLine', event.target.value)}
                placeholder="Contoh: Jl. Kebon Jeruk Raya No. 27"
                error={addressErrors.addressLine}
                className="mb-2.5"
            />
            <AddressFields data={data} setData={setData} errors={addressErrors} provinces={provinces} />
        </>
    ) : null;

    const consentField = isGuest ? (
        <>
            <label className="mb-3 flex items-start gap-2 text-[12px] leading-relaxed text-muted">
                <input
                    type="checkbox"
                    checked={data.consent}
                    onChange={(event) => setData('consent', event.target.checked)}
                    className="mt-0.5"
                />
                <span>
                    Saya sudah membaca dan menyetujui{' '}
                    <Link href="/syarat-ketentuan" className="text-brand underline">Syarat &amp; Ketentuan</Link>{' '}
                    dan{' '}
                    <Link href="/kebijakan-privasi" className="text-brand underline">Kebijakan Privasi</Link>{' '}
                    Inofarma.
                </span>
            </label>
            {addressErrors.consent ? <p className="-mt-2 mb-3 text-[11px] text-danger">{addressErrors.consent}</p> : null}
        </>
    ) : null;

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
                    <div className="mb-4 flex items-baseline justify-between">
                        <h2 className="font-display text-[22px]">Kontak</h2>
                        {isGuest ? <Link href="/signin" className="text-[13px] text-link underline">Masuk</Link> : null}
                    </div>
                    {isGuest ? (
                        <div className="mb-8">{contactFields}</div>
                    ) : (
                        <div className="mb-8 border border-line px-4 py-3.5 text-[13px]">
                            <div className="font-bold">{customerName}</div>
                            <div className="text-muted">{customerEmail}</div>
                        </div>
                    )}

                    {typingAddress ? (
                        <>
                            <h2 className="mb-4 font-display text-[22px]">Alamat</h2>
                            <div className="mb-8">{addressFields}</div>
                        </>
                    ) : null}

                    <h2 className="mb-4 font-display text-[22px]">Pengantaran</h2>
                    <div className="mb-4 flex gap-1 rounded-[2px] bg-[#f5f5f5] p-1">
                        {branch.supportsDelivery ? (
                            <button type="button" onClick={() => chooseFulfilment('antar')} className={choice(fulfilment === 'antar')}>
                                <Icon name="bagSimple" size={18} />
                                Kirim ke alamat
                            </button>
                        ) : null}
                        {canPickup ? (
                            <button type="button" onClick={() => chooseFulfilment('ambil')} className={choice(fulfilment === 'ambil')}>
                                <Icon name="pin" size={18} />
                                Ambil di toko
                            </button>
                        ) : null}
                    </div>
                    {errors.fulfilment ? <p className="mb-3 text-[12px] text-danger">{errors.fulfilment}</p> : null}

                    {fulfilment === 'antar' ? (
                        <>
                            {cart.address ? (
                                <Link href="/shipping-details" className="mb-6 block border border-line px-4 py-3.5">
                                    <div className="mb-1 flex items-center justify-between text-[13px] font-bold">
                                        <span>Alamat pengiriman</span>
                                        <Icon name="edit" size={16} />
                                    </div>
                                    <span className="text-[13px] text-muted">{cart.address.fullAddress}</span>
                                </Link>
                            ) : null}

                            {addressReady ? (
                                <>
                                    <h2 className="mb-3 font-display text-[18px]">Metode pengiriman</h2>
                                    {courierLoading ? (
                                        <p className="mb-6 text-[13px] text-muted">Memuat pilihan kurir…</p>
                                    ) : courierOptions.length === 0 ? (
                                        <p className="mb-6 text-[13px] text-danger">
                                            {courierError ?? 'Tidak ada kurir yang menjangkau alamat ini. Coba alamat lain.'}
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
                            {pickupDetails}
                        </div>
                    )}
                    {errors.address ? <p className="mb-3 text-[12px] text-danger">{errors.address}</p> : null}
                    {errors.courier ? <p className="mb-3 text-[12px] text-danger">{errors.courier}</p> : null}

                    <h2 className="mb-3 font-display text-[18px]">Metode pembayaran</h2>
                    <div className="mb-6 border-2 border-brand px-4 py-3.5 text-[13px]">
                        <div className="font-bold text-brand">Bayar Online (DOKU)</div>
                        <p className="mt-1 text-muted">
                            Anda akan diarahkan ke halaman pembayaran DOKU: virtual account bank, QRIS, atau e-wallet.
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

                    {consentField}

                    <div className="flex items-center justify-between">
                        <Link href="/cart" className="text-[13px] text-link">&lsaquo; Kembali ke keranjang</Link>
                        <button
                            type="submit"
                            disabled={cannotSubmit}
                            className="h-[52px] bg-success px-8 text-[14px] font-bold text-white disabled:opacity-60"
                        >
                            {processing ? 'Memproses pesanan…' : 'Lanjut ke Pembayaran'}
                        </button>
                    </div>
                </form>
                {locationModal}
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
                {isGuest || typingAddress ? (
                    <div className="mb-2 border border-line bg-lilac p-3.5">
                        <div className="mb-2.5 flex items-baseline justify-between border-b border-line pb-2 font-display text-[13px]">
                            <span>{isGuest ? 'Kontak & alamat' : 'Alamat'}</span>
                            {isGuest ? <Link href="/signin" className="text-[11px] text-brand underline">Masuk</Link> : null}
                        </div>
                        {contactFields}
                        {addressFields}
                    </div>
                ) : null}

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

                    {canPickup ? (
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
                        {cart.address ? (
                            <Link
                                href="/shipping-details"
                                className="mb-2 block border border-line bg-lilac p-3.5"
                            >
                                <div className="mb-2 flex items-center justify-between border-b border-line pb-2 font-display text-[13px]">
                                    <span>Detail pengiriman</span>
                                    <Icon name="edit" size={15} className="text-ink" />
                                </div>

                                <span className="text-xs text-muted">{cart.address.fullAddress}</span>
                            </Link>
                        ) : null}

                        {addressReady ? (
                            <div className="mb-2 border border-line bg-lilac p-3.5">
                                <div className="mb-2 border-b border-line pb-2 font-display text-[13px]">
                                    Pilih kurir
                                </div>

                                {courierLoading ? (
                                    <p className="text-xs text-muted">Memuat pilihan kurir…</p>
                                ) : courierOptions.length === 0 ? (
                                    <p className="text-xs text-danger">
                                        {courierError ?? 'Tidak ada kurir yang menjangkau alamat ini. Coba alamat lain.'}
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
                        {pickupDetails}
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
                        Anda akan diarahkan ke halaman pembayaran DOKU, pilih virtual account bank,
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

                {consentField}

                <Button
                    type="submit"
                    disabled={cannotSubmit}
                    className="mb-2"
                >
                    {processing ? 'Memproses pesanan…' : `Lanjut ke Pembayaran (${money(total)})`}
                </Button>
            </form>
            {locationModal}
        </MobileLayout>
    );
}
