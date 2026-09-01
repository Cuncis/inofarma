import { useEffect, useState } from 'react';
import Icon from './Icon';
import LocationPickerModal from './LocationPickerModal';
import useLocationConsent from './useLocationConsent';

/**
 * One level of the Provinsi → Kota → Kecamatan → Kelurahan cascade — a
 * plain `<select>` styled like `Field`, one per line so a long region name
 * never runs into the native dropdown caret the way a cramped 2-column
 * layout did. Stays white/normal-looking even before its parent level has a
 * value — see `onFocus` on the caller's side for how "pick that first" gets
 * enforced without greying the field out.
 *
 * @param {{
 *   id: string, label: string, value: string, onChange: (event: import('react').ChangeEvent<HTMLSelectElement>) => void,
 *   onFocus?: () => void, options: { code: string, name: string }[], placeholder: string, error?: string,
 * }} props
 */
function RegionSelect({ id, label, value, onChange, onFocus, options, placeholder, error }) {
    return (
        <div>
            <label htmlFor={id} className="mb-1 block text-[12px] font-medium text-ink">
                {label}
            </label>

            <select
                id={id}
                value={value}
                onChange={onChange}
                onFocus={onFocus}
                className={`h-control w-full truncate border bg-white px-3.5 text-[13px] text-muted focus:outline-none focus:ring-0 ${
                    error ? 'border-danger' : 'border-blush'
                }`}
            >
                <option value="">{placeholder}</option>
                {options.map((option) => (
                    <option key={option.code} value={option.code}>
                        {option.name}
                    </option>
                ))}
            </select>

            {error ? <p className="mt-1 text-[11px] text-danger">{error}</p> : null}
        </div>
    );
}

/**
 * Fetches the children of one region `code` from `RegionController`, same
 * fetch-on-dependency-change shape as `Checkout.jsx`'s courier-rates call.
 * Returns `[]` immediately (and skips the request) when `parentCode` is empty
 * — that's "no parent picked yet", not "parent has no children".
 */
function useRegionOptions(parentCode) {
    const [options, setOptions] = useState([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (! parentCode) {
            setOptions([]);

            return;
        }

        let cancelled = false;
        setLoading(true);

        fetch(`/ui/wilayah?parent=${parentCode}`, { headers: { Accept: 'application/json' } })
            .then((response) => response.json())
            .then((body) => {
                if (! cancelled) {
                    setOptions(body.options ?? []);
                }
            })
            .finally(() => {
                if (! cancelled) {
                    setLoading(false);
                }
            });

        return () => {
            cancelled = true;
        };
    }, [parentCode]);

    return [options, loading];
}

/**
 * The Provinsi/Kota/Kecamatan/Kelurahan/Kode Pos cascade plus the "Gunakan
 * lokasi saya" geolocation button — the whole region-picking block shared by
 * `Shop/AddNewAddress.jsx` (a signed-in customer's saved-address form) and
 * `Shop/GuestCheckout.jsx` (a guest's one-time checkout details). Owns the
 * cascade's region-code state internally; writes the plain names/postal
 * code/coordinates the caller's form actually submits back through `setData`
 * (an Inertia `useForm` setter, called in its functional-updater form so
 * this drops into either caller's `data` shape without them wiring anything
 * extra).
 *
 * @param {{
 *   data: { provinsi: string, kota: string, kecamatan: string, kelurahan: string, postalCode: string, latitude: ?number, longitude: ?number },
 *   setData: (updater: (current: object) => object) => void,
 *   errors: { provinsi?: string, kota?: string },
 *   provinces: { code: string, name: string }[],
 * }} props
 */
export default function AddressFields({ data, setData, errors, provinces }) {
    // The cascade is driven by region *codes*, kept separate from `data`
    // (which holds the plain names/postal code that actually get submitted
    // — `CustomerAddress` stores free text, not codes, so existing
    // saved-address screens and checkout never need to know regions exist).
    const [provinsiCode, setProvinsiCode] = useState('');
    const [kotaCode, setKotaCode] = useState('');
    const [kecamatanCode, setKecamatanCode] = useState('');
    const [kelurahanCode, setKelurahanCode] = useState('');

    const [kotaOptions, kotaLoading] = useRegionOptions(provinsiCode);
    const [kecamatanOptions, kecamatanLoading] = useRegionOptions(kotaCode);
    const [kelurahanOptions, kelurahanLoading] = useRegionOptions(kecamatanCode);

    // A level stays white/normal instead of greyed-out disabled, so opening
    // it out of order isn't blocked — it just has nothing but the
    // placeholder to pick, since its parent hasn't been chosen yet. These
    // flip to true the moment the shopper focuses that field early, so the
    // "pick the parent first" message only shows once it's actually
    // relevant, not on a fresh, untouched form.
    const [kotaTouchedEarly, setKotaTouchedEarly] = useState(false);
    const [kecamatanTouchedEarly, setKecamatanTouchedEarly] = useState(false);
    const [kelurahanTouchedEarly, setKelurahanTouchedEarly] = useState(false);

    const chooseProvinsi = (event) => {
        const option = provinces.find((province) => province.code === event.target.value);

        setProvinsiCode(option?.code ?? '');
        setKotaCode('');
        setKecamatanCode('');
        setKelurahanCode('');
        setData((current) => ({
            ...current,
            provinsi: option?.name ?? '',
            kota: '',
            kecamatan: '',
            kelurahan: '',
            postalCode: '',
        }));
    };

    const chooseKota = (event) => {
        const option = kotaOptions.find((kota) => kota.code === event.target.value);

        setKotaCode(option?.code ?? '');
        setKecamatanCode('');
        setKelurahanCode('');
        setData((current) => ({ ...current, kota: option?.name ?? '', kecamatan: '', kelurahan: '', postalCode: '' }));
    };

    const chooseKecamatan = (event) => {
        const option = kecamatanOptions.find((kecamatan) => kecamatan.code === event.target.value);

        setKecamatanCode(option?.code ?? '');
        setKelurahanCode('');
        setData((current) => ({ ...current, kecamatan: option?.name ?? '', kelurahan: '', postalCode: '' }));
    };

    const chooseKelurahan = (event) => {
        const option = kelurahanOptions.find((kelurahan) => kelurahan.code === event.target.value);

        setKelurahanCode(option?.code ?? '');
        setData((current) => ({
            ...current,
            kelurahan: option?.name ?? '',
            // Each kelurahan/desa has exactly one official postal code in
            // this dataset — it's derived, not a separate free choice.
            postalCode: option?.postalCode ?? '',
        }));
    };

    const [pickerOpen, setPickerOpen] = useState(false);
    const { consented, consent } = useLocationConsent();

    /**
     * Opens the map picker rather than calling `navigator.geolocation`
     * directly — the Geolocation API alone is unreliable across devices and
     * browsers (permission prompts, in-app browsers, disabled OS location
     * services all fail it in ways a shopper can't work around), so picking
     * a point by hand on `LocationPickerModal`'s map is always available; a
     * device fix there is only a shortcut, never a requirement.
     */
    const openLocationPicker = () => {
        if (! consented) {
            return;
        }

        setPickerOpen(true);
    };

    const confirmLocation = (lat, lng) => {
        setData((current) => ({ ...current, latitude: lat, longitude: lng }));
        setPickerOpen(false);
    };

    return (
        <>
            {/*
             * Broad-to-narrow order (Provinsi → Kota → Kecamatan →
             * Kelurahan), one per line — a 2-column grid cramped long
             * region names into the native dropdown caret. Each level's
             * options are scoped to the one picked before it — real
             * Kemendagri region data (see `regions:import`), not free
             * text anymore. Kode Pos isn't a 5th independent choice:
             * every kelurahan/desa has exactly one official postal code,
             * so it's derived and read-only.
             */}
            <div className="mb-2.5 flex flex-col gap-2.5">
                <RegionSelect
                    id="provinsi"
                    label="Provinsi"
                    value={provinsiCode}
                    onChange={chooseProvinsi}
                    options={provinces}
                    placeholder="Pilih provinsi"
                    error={errors.provinsi}
                />

                <RegionSelect
                    id="kota"
                    label="Kota / Kabupaten"
                    value={kotaCode}
                    onChange={chooseKota}
                    onFocus={() => setKotaTouchedEarly(! provinsiCode)}
                    options={kotaOptions}
                    placeholder={kotaLoading ? 'Memuat…' : 'Pilih kota/kabupaten'}
                    error={
                        errors.kota ??
                        (kotaTouchedEarly && ! provinsiCode ? 'Pilih provinsi terlebih dahulu.' : undefined)
                    }
                />

                <RegionSelect
                    id="kecamatan"
                    label="Kecamatan"
                    value={kecamatanCode}
                    onChange={chooseKecamatan}
                    onFocus={() => setKecamatanTouchedEarly(! kotaCode)}
                    options={kecamatanOptions}
                    placeholder={kecamatanLoading ? 'Memuat…' : 'Pilih kecamatan'}
                    error={
                        kecamatanTouchedEarly && ! kotaCode
                            ? 'Pilih kota/kabupaten terlebih dahulu.'
                            : undefined
                    }
                />

                <RegionSelect
                    id="kelurahan"
                    label="Kelurahan"
                    value={kelurahanCode}
                    onChange={chooseKelurahan}
                    onFocus={() => setKelurahanTouchedEarly(! kecamatanCode)}
                    options={kelurahanOptions}
                    placeholder={kelurahanLoading ? 'Memuat…' : 'Pilih kelurahan'}
                    error={
                        kelurahanTouchedEarly && ! kecamatanCode
                            ? 'Pilih kecamatan terlebih dahulu.'
                            : undefined
                    }
                />

                <div>
                    <label htmlFor="postalCode" className="mb-1 block text-[12px] font-medium text-ink">
                        Kode Pos
                    </label>

                    <input
                        id="postalCode"
                        value={data.postalCode}
                        readOnly
                        placeholder="Terisi otomatis"
                        className="h-control w-full border border-blush bg-white px-3.5 text-[13px] text-muted focus:outline-none focus:ring-0"
                    />
                </div>
            </div>

            {! consented ? (
                <label className="mb-2.5 flex items-start gap-2 text-[11px] leading-relaxed text-muted">
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
                onClick={openLocationPicker}
                disabled={! consented}
                aria-describedby={! consented ? 'location-consent-hint' : undefined}
                className={`flex w-full items-center gap-3 rounded-lg border border-line bg-white p-3.5 text-left disabled:opacity-60 ${
                    consented ? 'mb-2.5' : 'mb-1'
                }`}
            >
                <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blush text-brand">
                    <Icon name="navigation" size={18} />
                </span>

                <span className="min-w-0 flex-1">
                    <span className="block text-[13px] font-bold text-ink">
                        {data.latitude ? 'Lokasi tersimpan, perbarui' : 'Gunakan lokasi saya'}
                    </span>
                    <span className="block text-[11px] text-muted">
                        Membantu menghitung ongkir dan radius antar dari cabang.
                    </span>
                </span>

                {data.latitude ? (
                    <Icon name="check" size={16} className="shrink-0 text-success" />
                ) : (
                    <Icon name="chevronRight" size={14} className="shrink-0 text-[#cccccc]" />
                )}
            </button>

            {! consented ? (
                <p id="location-consent-hint" className="mb-2.5 text-[11px] text-faint">
                    Setujui berbagi lokasi di atas terlebih dahulu untuk mengaktifkan tombol ini.
                </p>
            ) : null}

            <LocationPickerModal
                open={pickerOpen}
                initialLat={data.latitude}
                initialLng={data.longitude}
                onClose={() => setPickerOpen(false)}
                onConfirm={confirmLocation}
            />
        </>
    );
}
