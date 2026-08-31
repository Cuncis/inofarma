import { Link, useForm } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AddressFields from '@/Components/Shop/AddressFields';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Field from '@/Components/Shop/Field';

/**
 * Checkout without signing in first. Collects the same details a saved
 * address + account would already carry for a returning customer — name,
 * phone, email, delivery address — in one pass; submitting hands off to the
 * normal checkout flow (`GuestCheckoutController::store()` creates and signs
 * in a real account behind the scenes, so nothing past this screen needs to
 * know the shopper started as a guest).
 *
 * @param {{ provinces: { code: string, name: string }[] }} props
 */
export default function GuestCheckout({ provinces }) {
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

    const submit = (event) => {
        event.preventDefault();

        post('/ui/checkout/tamu');
    };

    return (
        <MobileLayout
            title="Checkout Sebagai Tamu"
            header={<AppBar title="Checkout Sebagai Tamu" back="/ui/cart" tone="brand" />}
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
                        <Link href="/ui/syarat-ketentuan" className="text-brand underline">
                            Syarat &amp; Ketentuan
                        </Link>{' '}
                        dan{' '}
                        <Link href="/ui/kebijakan-privasi" className="text-brand underline">
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
                    <Link href="/ui/signin" className="text-brand">
                        Masuk di sini.
                    </Link>
                </div>
            </form>
        </MobileLayout>
    );
}
