import type { FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AddressFields from '@/Components/Shop/AddressFields';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import DesktopCheckoutShell, { CheckoutSummary } from '@/Components/Shop/DesktopCheckoutShell';
import Field from '@/Components/Shop/Field';
import useIsDesktop from '@/Components/Shop/useIsDesktop';

/**
 * Checkout without signing in first. Collects the same details a saved
 * address + account would already carry for a returning customer — name,
 * phone, email, delivery address — in one pass; submitting hands off to the
 * normal checkout flow (`GuestCheckoutController::store()` creates and signs
 * in a real account behind the scenes, so nothing past this screen needs to
 * know the shopper started as a guest).
 */
export default function GuestCheckout({ provinces, cart }: {
  provinces: { code: string, name: string }[],
  cart: { items: CartPreviewItem[], subtotal: number, discount: number, coupon: CartCoupon | null },
}) {
    const isDesktop = useIsDesktop();
    // Keyed so the in-progress form survives a trip to Syarat & Ketentuan /
    // Kebijakan Privasi and back — Inertia persists remembered data in
    // browser history state, restored on the back navigation `AppBar`
    // triggers from those pages.
    const { data, setData, post, processing, errors } = useForm('guest-checkout-form', {
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
        latitude: null,
        longitude: null,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        post('/checkout/tamu');
    };

    if (isDesktop) {
        const total = Math.max(cart.subtotal - cart.discount, 0);

        return (
            <DesktopCheckoutShell
                title="Checkout Sebagai Tamu"
                summary={
                    <CheckoutSummary
                        items={cart.items}
                        subtotal={cart.subtotal}
                        discount={cart.discount}
                        couponCode={cart.coupon?.code}
                        shippingLabel="Masukkan alamat pengiriman"
                        total={total}
                    />
                }
            >
                <form onSubmit={submit} autoComplete="off">
                    <div className="mb-4 flex items-baseline justify-between">
                        <h2 className="font-display text-[22px]">Kontak</h2>
                        <Link href="/signin" className="text-[13px] text-link underline">Masuk</Link>
                    </div>

                    <Field
                        type="email"
                        name="email"
                        label="Email"
                        value={data.email}
                        onChange={(event) => setData('email', event.target.value)}
                        placeholder="Contoh: kirana.wijaya@mail.com"
                        error={errors.email}
                        autoComplete="off"
                        className="mb-3"
                    />

                    <h2 className="mb-4 mt-8 font-display text-[22px]">Pengantaran</h2>

                    <div className="mb-3 grid grid-cols-2 gap-3">
                        <Field
                            name="name"
                            label="Nama Lengkap"
                            value={data.name}
                            onChange={(event) => setData('name', event.target.value)}
                            placeholder="Contoh: Kirana Wijaya"
                            error={errors.name}
                            autoComplete="off"
                        />
                        <Field
                            type="tel"
                            name="phone"
                            label="Telepon"
                            value={data.phone}
                            onChange={(event) => setData('phone', event.target.value)}
                            placeholder="Contoh: 081234567890"
                            error={errors.phone}
                            autoComplete="off"
                        />
                    </div>

                    <Field
                        name="addressLine"
                        label="Alamat"
                        value={data.addressLine}
                        onChange={(event) => setData('addressLine', event.target.value)}
                        placeholder="Contoh: Jl. Kebon Jeruk Raya No. 27"
                        error={errors.addressLine}
                        className="mb-3"
                    />

                    <AddressFields data={data} setData={setData} errors={errors} provinces={provinces} />

                    <label className="mb-4 mt-2 flex items-start gap-2 text-[12px] leading-relaxed text-muted">
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
                    {errors.consent ? <p className="-mt-2 mb-3 text-[11px] text-danger">{errors.consent}</p> : null}

                    <div className="flex items-center justify-between pt-2">
                        <Link href="/cart" className="text-[13px] text-link">&lsaquo; Kembali ke keranjang</Link>
                        <button
                            type="submit"
                            disabled={processing || ! data.consent}
                            className="h-[52px] bg-success px-8 text-[14px] font-bold text-white disabled:opacity-60"
                        >
                            {processing ? 'Memproses…' : 'Lanjutkan ke Pembayaran'}
                        </button>
                    </div>
                </form>
            </DesktopCheckoutShell>
        );
    }

    return (
        <MobileLayout
            title="Checkout Sebagai Tamu"
            header={<AppBar title="Checkout Sebagai Tamu" back="/cart" tone="brand" />}
        >
            <form onSubmit={submit} autoComplete="off" className="flex-1 overflow-y-auto bg-canvas p-4">
                <p className="mb-[18px] text-[13px] leading-relaxed text-muted">
                    Isi detail Anda untuk melanjutkan tanpa membuat akun terlebih dahulu.
                    Pesanan akan dikirim ke email dan nomor di bawah ini.
                </p>

                <Field
                    name="name"
                    label="Nama Lengkap"
                    value={data.name}
                    onChange={(event) => setData('name', event.target.value)}
                    placeholder="Contoh: Kirana Wijaya"
                    error={errors.name}
                    autoComplete="off"
                    className="mb-2.5"
                />

                <Field
                    type="tel"
                    name="phone"
                    label="Nomor Telepon"
                    value={data.phone}
                    onChange={(event) => setData('phone', event.target.value)}
                    placeholder="Contoh: 081234567890"
                    error={errors.phone}
                    autoComplete="off"
                    className="mb-2.5"
                />

                <Field
                    type="email"
                    name="email"
                    label="Email"
                    value={data.email}
                    onChange={(event) => setData('email', event.target.value)}
                    placeholder="Contoh: kirana.wijaya@mail.com"
                    error={errors.email}
                    autoComplete="off"
                    className="mb-2.5"
                />

                <Field
                    name="addressLine"
                    label="Alamat Lengkap"
                    value={data.addressLine}
                    onChange={(event) => setData('addressLine', event.target.value)}
                    placeholder="Contoh: Jl. Kebon Jeruk Raya No. 27"
                    error={errors.addressLine}
                    className="mb-2.5"
                />

                <AddressFields data={data} setData={setData} errors={errors} provinces={provinces} />

                <label className="mb-3.5 flex items-start gap-2 text-[11px] leading-relaxed text-muted">
                    <input
                        type="checkbox"
                        checked={data.consent}
                        onChange={(event) => setData('consent', event.target.checked)}
                        className="mt-0.5"
                    />
                    <span>
                        Saya sudah membaca dan menyetujui{' '}
                        <Link href="/syarat-ketentuan" className="text-brand underline">
                            Syarat &amp; Ketentuan
                        </Link>{' '}
                        dan{' '}
                        <Link href="/kebijakan-privasi" className="text-brand underline">
                            Kebijakan Privasi
                        </Link>{' '}
                        Inofarma.
                    </span>
                </label>
                {errors.consent ? (
                    <p className="-mt-2.5 mb-2.5 text-[11px] text-danger">{errors.consent}</p>
                ) : null}

                <Button type="submit" disabled={processing || ! data.consent}>
                    {processing ? 'Memproses…' : 'Lanjutkan ke Pembayaran'}
                </Button>

                <div className="mt-2.5 flex justify-center gap-1 text-xs">
                    <span>Sudah punya akun?</span>
                    <Link href="/signin" className="text-brand">
                        Masuk di sini.
                    </Link>
                </div>
            </form>
        </MobileLayout>
    );
}
