import { useState } from 'react';
import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import geolocationErrorMessage from '@/Components/Shop/geolocationError';
import Icon from '@/Components/Shop/Icon';
import useLocationConsent from '@/Components/Shop/useLocationConsent';

/**
 * "Cabang Kami" — every branch, nearest first once we know where the shopper
 * is. Location comes from the browser's geolocation prompt or, for the many
 * people who decline it, a manual province/city picker built from real
 * coverage areas. Either way the choice is saved server-side (`ui.lokasi.store`)
 * so it isn't asked again next visit.
 */
export default function OurBranches({ branches, areas, hasLocation }) {
    const [locating, setLocating] = useState(false);
    const [locationError, setLocationError] = useState('');
    const { consented, consent } = useLocationConsent();

    const useMyLocation = () => {
        if (! consented) {
            return;
        }

        if (! navigator.geolocation) {
            setLocationError('Perangkat ini tidak mendukung layanan lokasi.');

            return;
        }

        // The Geolocation API is blocked outright on an insecure origin
        // (plain HTTP, other than localhost) — the browser fails every call
        // with the same generic permission-denied error, which otherwise
        // looks identical to the user actually having said no.
        if (! window.isSecureContext) {
            setLocationError('Fitur lokasi hanya berfungsi lewat HTTPS (atau localhost). Pilih area terdekat secara manual di bawah.');

            return;
        }

        setLocating(true);
        setLocationError('');

        navigator.geolocation.getCurrentPosition(
            (position) => {
                router.post(
                    '/ui/lokasi',
                    { lat: position.coords.latitude, lng: position.coords.longitude },
                    { preserveScroll: true, onFinish: () => setLocating(false) },
                );
            },
            (error) => {
                setLocating(false);
                setLocationError(`${geolocationErrorMessage(error)} Pilih area terdekat secara manual di bawah.`);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 },
        );
    };

    const pickArea = (event) => {
        const [provinsi, kota] = event.target.value.split('|');

        if (! kota) {
            return;
        }

        router.post('/ui/lokasi', { provinsi, kota }, { preserveScroll: true });
    };

    return (
        <MobileLayout title="Cabang Kami" header={<AppBar title="Cabang Kami" back="/ui/profile" tone="brand" />}>
            <div className="flex-1 overflow-y-auto px-3.5 pb-[90px] pt-3.5">
                {! hasLocation ? (
                    <div className="mb-3.5 border border-line bg-blush p-3.5">
                        <p className="mb-2.5 text-[13px] leading-relaxed">
                            Urutkan berdasarkan jarak dari lokasi Anda.
                        </p>

                        <label className="mb-2.5 flex items-start gap-2 text-[11px] leading-relaxed text-muted">
                            <input
                                type="checkbox"
                                checked={consented}
                                onChange={(event) => (event.target.checked ? consent() : null)}
                                className="mt-0.5"
                            />
                            <span>Saya setuju berbagi lokasi perangkat saya untuk menemukan cabang terdekat.</span>
                        </label>

                        <button
                            type="button"
                            onClick={useMyLocation}
                            disabled={locating || ! consented}
                            className="mb-2.5 flex w-full items-center justify-center gap-2 bg-ink py-2.5 text-[13px] font-bold text-white disabled:opacity-60"
                        >
                            <Icon name="navigation" size={16} className="text-white" />
                            {locating ? 'Mencari lokasi…' : 'Gunakan Lokasi Saya'}
                        </button>

                        {locationError ? (
                            <p className="mb-2.5 text-xs text-danger">{locationError}</p>
                        ) : null}

                        <select
                            defaultValue=""
                            onChange={pickArea}
                            className="w-full border border-line bg-white p-2.5 text-[13px]"
                        >
                            <option value="" disabled>
                                Atau pilih area terdekat…
                            </option>
                            {areas.map((area) => (
                                <option key={`${area.provinsi}|${area.kota}`} value={`${area.provinsi}|${area.kota}`}>
                                    {area.kota}, {area.provinsi}
                                </option>
                            ))}
                        </select>
                    </div>
                ) : null}

                {branches.map((branch) => (
                    <div key={branch.id} className="mb-2.5 rounded-lg border border-line bg-white p-3.5">
                        <div className="mb-1.5 flex items-start justify-between gap-2">
                            <div>
                                <div className="text-sm font-bold">{branch.name}</div>
                                <div className="text-xs text-muted">{branch.kota}</div>
                            </div>

                            {branch.distanceKm !== null ? (
                                <span className="shrink-0 whitespace-nowrap text-xs font-bold text-brand">
                                    {branch.distanceKm} km
                                </span>
                            ) : null}
                        </div>

                        <div className="mb-2.5 flex items-start gap-1.5 text-xs text-muted">
                            <Icon name="pin" size={14} className="mt-0.5 shrink-0" />
                            {branch.fullAddress}
                        </div>

                        <div className="flex flex-wrap items-center gap-2.5 text-xs">
                            <span
                                className={`inline-flex items-center gap-1 font-semibold ${
                                    branch.isOpenNow ? 'text-success-deep' : 'text-muted'
                                }`}
                            >
                                <Icon name="clock" size={13} />
                                {branch.isOpenNow ? 'Buka sekarang' : 'Tutup'}
                                {branch.todaysHours ? ` (${branch.todaysHours})` : ''}
                            </span>

                            {branch.supportsDelivery ? (
                                <span className="border border-line px-2 py-0.5 text-muted">Antar</span>
                            ) : null}
                            {branch.supportsPickup ? (
                                <span className="border border-line px-2 py-0.5 text-muted">Ambil di Tempat</span>
                            ) : null}
                        </div>

                        {branch.siaNumber || branch.apjName ? (
                            <div className="mt-2.5 border-t border-line pt-2.5 text-[11px] leading-relaxed text-muted">
                                {branch.siaNumber ? <div>SIA: {branch.siaNumber}</div> : null}
                                {branch.apjName ? (
                                    <div>
                                        APJ: {branch.apjName}
                                        {branch.apjSipaNumber ? ` (SIPA ${branch.apjSipaNumber})` : ''}
                                    </div>
                                ) : null}
                            </div>
                        ) : null}

                        <div className="mt-2.5 flex gap-3.5 border-t border-line pt-2.5">
                            {branch.phone ? (
                                <a
                                    href={`tel:${branch.phone.replace(/\s/g, '')}`}
                                    className="flex items-center gap-1 text-xs font-bold text-ink"
                                >
                                    <Icon name="phone" size={14} />
                                    Telepon
                                </a>
                            ) : null}

                            {branch.mapsUrl ? (
                                <a
                                    href={branch.mapsUrl}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="flex items-center gap-1 text-xs font-bold text-brand"
                                >
                                    <Icon name="navigation" size={14} />
                                    Rute
                                </a>
                            ) : null}
                        </div>
                    </div>
                ))}

                {branches.length === 0 ? (
                    <p className="mt-10 text-center text-[13px] text-muted">
                        Belum ada cabang yang tercatat.
                    </p>
                ) : null}
            </div>
        </MobileLayout>
    );
}
