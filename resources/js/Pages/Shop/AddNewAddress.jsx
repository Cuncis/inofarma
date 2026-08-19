import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Field from '@/Components/Shop/Field';
import useLocationConsent from '@/Components/Shop/useLocationConsent';

export default function AddNewAddress() {
    const { data, setData, post, processing, errors } = useForm({
        label: '',
        recipientName: '',
        phone: '',
        addressLine: '',
        kelurahan: '',
        kecamatan: '',
        kota: '',
        provinsi: '',
        postalCode: '',
        note: '',
        latitude: null,
        longitude: null,
    });

    const [locating, setLocating] = useState(false);
    const [locationError, setLocationError] = useState('');
    const { consented, consent } = useLocationConsent();

    /**
     * Fills the coordinates from the browser's own location, same API
     * `LocationController`/`BranchPicker` already use elsewhere in the shop.
     * The rest of the address stays free text — see `.ai/rules` for why
     * (there is no real provinsi → kota → kecamatan dataset in this repo yet).
     */
    const useMyLocation = () => {
        if (! consented) {
            return;
        }

        if (! navigator.geolocation) {
            setLocationError('Perangkat ini tidak mendukung deteksi lokasi.');

            return;
        }

        setLocating(true);
        setLocationError('');

        navigator.geolocation.getCurrentPosition(
            (position) => {
                setData((current) => ({
                    ...current,
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                }));
                setLocating(false);
            },
            () => {
                setLocationError('Tidak bisa mengambil lokasi. Isi alamat secara manual.');
                setLocating(false);
            },
        );
    };

    const submit = (event) => {
        event.preventDefault();

        post('/ui/add-new-address');
    };

    return (
        <MobileLayout
            title="Tambah Alamat Baru"
            header={
                <AppBar title="Tambah Alamat Baru" back="/ui/my-address" tone="white" />
            }
        >
            <form onSubmit={submit} className="flex-1 overflow-y-auto p-4">
                <Field
                    name="label"
                    value={data.label}
                    onChange={(event) => setData('label', event.target.value)}
                    placeholder="Label alamat (mis. Rumah)"
                    error={errors.label}
                    className="mb-2.5"
                />

                <Field
                    name="recipientName"
                    value={data.recipientName}
                    onChange={(event) => setData('recipientName', event.target.value)}
                    placeholder="Nama penerima"
                    error={errors.recipientName}
                    className="mb-2.5"
                />

                <Field
                    name="phone"
                    value={data.phone}
                    onChange={(event) => setData('phone', event.target.value)}
                    placeholder="Nomor HP penerima"
                    error={errors.phone}
                    className="mb-2.5"
                />

                <Field
                    name="addressLine"
                    value={data.addressLine}
                    onChange={(event) => setData('addressLine', event.target.value)}
                    placeholder="Alamat lengkap (jalan, no. rumah)"
                    error={errors.addressLine}
                    className="mb-2.5"
                />

                <Field
                    name="kelurahan"
                    value={data.kelurahan}
                    onChange={(event) => setData('kelurahan', event.target.value)}
                    placeholder="Kelurahan"
                    className="mb-2.5"
                />

                <Field
                    name="kecamatan"
                    value={data.kecamatan}
                    onChange={(event) => setData('kecamatan', event.target.value)}
                    placeholder="Kecamatan"
                    className="mb-2.5"
                />

                <Field
                    name="kota"
                    value={data.kota}
                    onChange={(event) => setData('kota', event.target.value)}
                    placeholder="Kota / Kabupaten"
                    error={errors.kota}
                    className="mb-2.5"
                />

                <Field
                    name="provinsi"
                    value={data.provinsi}
                    onChange={(event) => setData('provinsi', event.target.value)}
                    placeholder="Provinsi"
                    error={errors.provinsi}
                    className="mb-2.5"
                />

                <Field
                    name="postalCode"
                    value={data.postalCode}
                    onChange={(event) => setData('postalCode', event.target.value)}
                    placeholder="Kode pos"
                    inputMode="numeric"
                    className="mb-2.5"
                />

                {! consented ? (
                    <label className="mb-2 flex items-start gap-2 text-[11px] leading-relaxed text-muted">
                        <input
                            type="checkbox"
                            checked={consented}
                            onChange={(event) => (event.target.checked ? consent() : null)}
                            className="mt-0.5"
                        />
                        <span>Saya setuju berbagi lokasi perangkat saya untuk mengisi alamat ini.</span>
                    </label>
                ) : null}

                <button
                    type="button"
                    onClick={useMyLocation}
                    disabled={locating || ! consented}
                    className="mb-1 text-xs font-semibold text-brand disabled:opacity-60"
                >
                    {locating
                        ? 'Mengambil lokasi…'
                        : data.latitude
                          ? 'Lokasi tersimpan ✓ — perbarui'
                          : '📍 Gunakan lokasi saya'}
                </button>

                {locationError ? (
                    <p className="mb-2.5 text-[11px] text-danger">{locationError}</p>
                ) : (
                    <p className="mb-2.5 text-[11px] text-muted">
                        Membantu menghitung ongkir dan radius antar dari cabang.
                    </p>
                )}

                <Button type="submit" disabled={processing}>
                    {processing ? 'Menyimpan…' : 'Tambah Alamat'}
                </Button>
            </form>
        </MobileLayout>
    );
}
