import { useEffect, useRef, useState } from 'react';
import geolocationErrorMessage from './geolocationError';
import Icon from './Icon';
import SearchBar from './SearchBar';

// Wide enough to show the whole archipelago until a point is picked or a
// device fix centers the map — there's no meaningful single default city to
// zoom to for every shopper.
const DEFAULT_CENTER = { lat: -2.5, lng: 118 };
const DEFAULT_ZOOM = 5;
const POINT_ZOOM = 16;

// Google's own usage guidance is to fire Autocomplete requests only after
// the shopper pauses typing, not on every keystroke.
const SEARCH_DEBOUNCE_MS = 700;

// Loaded once per page and reused by every mount — Google's script rejects
// being injected twice, and shoppers can open/close this modal repeatedly.
let googleMapsPromise = null;

function loadGoogleMaps() {
    if (window.google?.maps?.places) {
        return Promise.resolve(window.google.maps);
    }

    if (! googleMapsPromise) {
        googleMapsPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            const key = import.meta.env.VITE_GOOGLE_MAPS_API_KEY ?? '';

            script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(key)}&libraries=places`;
            script.async = true;
            script.onload = () => resolve(window.google.maps);
            script.onerror = () => reject(new Error('load-failed'));
            document.head.appendChild(script);
        });
    }

    return googleMapsPromise;
}

/**
 * Click-to-place-a-pin location picker, opened from `AddressFields.jsx`'s
 * "Gunakan lokasi saya" button.
 *
 * Uses Google Maps (JS API + Places Autocomplete) rather than a free tile
 * provider — shoppers already know how to read a Google map, which a
 * lesser-known basemap doesn't buy back. Needs `VITE_GOOGLE_MAPS_API_KEY` set
 * (an HTTP-referrer-restricted browser key, not a secret) with the "Maps
 * JavaScript API" and "Places API" enabled and billing active on the Google
 * Cloud project.
 *
 * Browser geolocation alone (`getCurrentPosition`) is unreliable across
 * devices and browsers — permission prompts, in-app browsers, disabled OS
 * location services all fail it in ways a shopper has no fix for beyond
 * "type the address in by hand." So a point can always be placed by hand
 * here, two ways: tapping the map directly, or typing an address into the
 * search bar and picking a result (Google Places Autocomplete, restricted to
 * Indonesia). The "Lokasi saya" button is a third, one-tap shortcut that only
 * works if the device's own location fix happens to succeed.
 *
 * Confirming is a real popup (dimmed backdrop, centered card) rather than an
 * inline panel — a bottom-of-map panel styled like the rest of the sheet
 * blended in and went unnoticed, so this is deliberately unmissable.
 *
 * Renders as a full-frame overlay, same positioning trick as
 * `SearchOverlay.jsx`: `absolute inset-0` resolves against `MobileLayout`'s
 * `relative` root, not the nearest ancestor, because every element between
 * here and there is `position: static` — so it escapes `AddressFields`'
 * scrolling `<form>` instead of being clipped to it.
 *
 * The map container and the floating buttons below it are siblings with no
 * z-index of their own — a positioned element without one doesn't start a
 * new stacking context, so the map's own internal layers (tiles, markers,
 * Google's own controls, all given real z-index values by the API) leak out
 * and paint above a same-context sibling regardless of DOM order. `z-0` on
 * the map container traps its internals in their own context; `z-10` on the
 * button row then unambiguously wins against that trapped context.
 *
 * @param {{
 *   open: boolean,
 *   initialLat?: number|null,
 *   initialLng?: number|null,
 *   onClose: () => void,
 *   onConfirm: (lat: number, lng: number) => void,
 * }} props
 */
export default function LocationPickerModal({ open, initialLat, initialLng, onClose, onConfirm }) {
    const containerRef = useRef(null);
    const mapRef = useRef(null);
    const markerRef = useRef(null);
    const autocompleteServiceRef = useRef(null);
    const placesServiceRef = useRef(null);
    const [point, setPoint] = useState(null);
    const [confirming, setConfirming] = useState(false);
    const [locating, setLocating] = useState(false);
    const [locateError, setLocateError] = useState('');
    const [mapError, setMapError] = useState('');

    const [query, setQuery] = useState('');
    const [searchResults, setSearchResults] = useState([]);
    const [searching, setSearching] = useState(false);
    const [searchError, setSearchError] = useState('');

    const placePoint = (lat, lng) => {
        setPoint({ lat, lng });

        if (markerRef.current) {
            markerRef.current.setPosition({ lat, lng });
        } else if (mapRef.current) {
            markerRef.current = new window.google.maps.Marker({
                position: { lat, lng },
                map: mapRef.current,
            });
        }
    };

    // Fresh state each time the modal opens, not whatever a previous
    // open/cancel left behind, then build the map itself against the now-
    // mounted container.
    useEffect(() => {
        if (! open || ! containerRef.current) {
            return;
        }

        setConfirming(false);
        setLocateError('');
        setMapError('');
        setQuery('');
        setSearchResults([]);
        setSearchError('');

        let cancelled = false;

        loadGoogleMaps()
            .then((maps) => {
                if (cancelled || ! containerRef.current) {
                    return;
                }

                const hasInitial = initialLat != null && initialLng != null;
                const center = hasInitial ? { lat: initialLat, lng: initialLng } : DEFAULT_CENTER;

                const map = new maps.Map(containerRef.current, {
                    center,
                    zoom: hasInitial ? POINT_ZOOM : DEFAULT_ZOOM,
                    streetViewControl: false,
                    fullscreenControl: false,
                    mapTypeControl: false,
                });

                mapRef.current = map;
                markerRef.current = null;
                autocompleteServiceRef.current = new maps.places.AutocompleteService();
                placesServiceRef.current = new maps.places.PlacesService(map);

                if (hasInitial) {
                    placePoint(initialLat, initialLng);
                } else {
                    setPoint(null);
                }

                map.addListener('click', (event) => placePoint(event.latLng.lat(), event.latLng.lng()));

                // The container can still measure as 0-height on the very
                // first paint (it just mounted this same tick) — Google Maps
                // then renders for that stale size. Nudging it to resize one
                // tick later fixes it reliably without a fragile fixed delay.
                setTimeout(() => {
                    if (cancelled) {
                        return;
                    }

                    maps.event.trigger(map, 'resize');
                    map.setCenter(center);
                }, 0);
            })
            .catch(() => {
                if (! cancelled) {
                    setMapError('Gagal memuat Google Maps. Periksa koneksi internet Anda.');
                }
            });

        return () => {
            cancelled = true;
            mapRef.current = null;
            markerRef.current = null;
            autocompleteServiceRef.current = null;
            placesServiceRef.current = null;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    // Debounced address search — skips anything under 3 characters so it
    // doesn't fire on a single keystroke, and cancels a stale in-flight
    // request/timer if the shopper keeps typing before it lands.
    useEffect(() => {
        const term = query.trim();

        if (term.length < 3) {
            setSearchResults([]);
            setSearchError('');

            return;
        }

        if (! autocompleteServiceRef.current) {
            return;
        }

        let cancelled = false;
        setSearching(true);

        const timer = setTimeout(() => {
            autocompleteServiceRef.current.getPlacePredictions(
                { input: term, componentRestrictions: { country: 'id' } },
                (predictions, status) => {
                    if (cancelled) {
                        return;
                    }

                    const ok = status === window.google.maps.places.PlacesServiceStatus.OK && predictions;

                    setSearchResults(ok ? predictions : []);
                    setSearchError(ok ? '' : 'Alamat tidak ditemukan.');
                    setSearching(false);
                },
            );
        }, SEARCH_DEBOUNCE_MS);

        return () => {
            cancelled = true;
            clearTimeout(timer);
        };
    }, [query]);

    const chooseSearchResult = (prediction) => {
        if (! placesServiceRef.current) {
            return;
        }

        placesServiceRef.current.getDetails({ placeId: prediction.place_id, fields: ['geometry'] }, (place, status) => {
            if (status === window.google.maps.places.PlacesServiceStatus.OK && place?.geometry?.location) {
                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();

                placePoint(lat, lng);
                mapRef.current?.setCenter({ lat, lng });
                mapRef.current?.setZoom(POINT_ZOOM);
            }
        });

        setSearchResults([]);
        setQuery(prediction.description);
    };

    const locateMe = () => {
        if (! navigator.geolocation) {
            setLocateError('Perangkat ini tidak mendukung deteksi lokasi.');

            return;
        }

        setLocating(true);
        setLocateError('');

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;

                placePoint(latitude, longitude);
                mapRef.current?.setCenter({ lat: latitude, lng: longitude });
                mapRef.current?.setZoom(POINT_ZOOM);
                setLocating(false);
            },
            (error) => {
                setLocateError(geolocationErrorMessage(error));
                setLocating(false);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 },
        );
    };

    if (! open) {
        return null;
    }

    return (
        <div className="absolute inset-0 z-50 flex flex-col bg-white">
            <div className="flex h-appbar shrink-0 items-center justify-between bg-brand px-3.5 text-white">
                <span className="font-display text-sm uppercase tracking-[0.5px]">Pilih Lokasi</span>

                <button
                    type="button"
                    onClick={onClose}
                    aria-label="Tutup"
                    className="flex h-9 w-9 items-center justify-center"
                >
                    <Icon name="close" size={20} />
                </button>
            </div>

            <div className="relative z-10 border-b border-line bg-white p-2.5">
                <SearchBar
                    value={query}
                    onChange={setQuery}
                    placeholder="Cari alamat, jalan, atau tempat..."
                    ariaLabel="Cari alamat"
                />

                {query.trim().length >= 3 ? (
                    <div className="absolute inset-x-2.5 top-full z-10 max-h-64 overflow-y-auto border border-t-0 border-line bg-white shadow-lg">
                        {searching ? (
                            <p className="p-3 text-center text-[11px] text-faint">Mencari…</p>
                        ) : searchError ? (
                            <p className="p-3 text-center text-[11px] text-faint">{searchError}</p>
                        ) : (
                            <ul>
                                {searchResults.map((prediction) => (
                                    <li key={prediction.place_id}>
                                        <button
                                            type="button"
                                            onClick={() => chooseSearchResult(prediction)}
                                            className="flex w-full items-start gap-2 border-b border-line px-3 py-2.5 text-left last:border-b-0"
                                        >
                                            <Icon
                                                name="pin"
                                                size={14}
                                                className="mt-0.5 shrink-0 text-brand"
                                            />
                                            <span className="text-[12px] leading-snug text-ink">
                                                {prediction.description}
                                            </span>
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                ) : null}
            </div>

            <div className="relative flex-1">
                <div ref={containerRef} className="absolute inset-0 z-0" />

                <div className="pointer-events-none absolute inset-x-0 bottom-0 z-10 flex flex-col items-end gap-2 p-3.5">
                    {mapError || locateError ? (
                        <p className="pointer-events-auto w-full rounded-lg bg-white p-2.5 text-center text-[11px] text-danger shadow">
                            {mapError || locateError}
                        </p>
                    ) : null}

                    <button
                        type="button"
                        onClick={locateMe}
                        disabled={locating}
                        className="pointer-events-auto flex h-10 items-center gap-2 rounded-full bg-white px-4 text-xs font-bold text-brand shadow disabled:opacity-60"
                    >
                        <Icon name="navigation" size={16} />
                        {locating ? 'Mencari…' : 'Lokasi saya'}
                    </button>

                    <button
                        type="button"
                        onClick={() => point && setConfirming(true)}
                        disabled={! point}
                        className="pointer-events-auto flex h-12 w-full items-center justify-center bg-ink text-xs font-bold uppercase tracking-wider text-white shadow disabled:opacity-40"
                    >
                        {point ? 'Gunakan Titik Ini' : 'Ketuk peta untuk memilih titik'}
                    </button>
                </div>
            </div>

            {confirming ? (
                <div className="absolute inset-0 z-20 flex items-center justify-center bg-ink/50 p-6">
                    <div className="w-full max-w-[300px] rounded-xl bg-white p-5 shadow-xl">
                        <span className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-blush text-brand">
                            <Icon name="pin" size={22} />
                        </span>

                        <p className="mb-4 text-center text-[13px] leading-relaxed text-ink">
                            Gunakan titik ini sebagai lokasi alamat Anda?
                        </p>

                        <div className="flex gap-2.5">
                            <button
                                type="button"
                                onClick={() => setConfirming(false)}
                                className="h-11 flex-1 border-2 border-ink text-[11px] font-bold uppercase tracking-wider text-ink"
                            >
                                Batal
                            </button>

                            <button
                                type="button"
                                onClick={() => onConfirm(point.lat, point.lng)}
                                className="flex h-11 flex-1 items-center justify-center gap-1.5 bg-brand text-[11px] font-bold uppercase tracking-wider text-white"
                            >
                                <Icon name="check" size={13} />
                                Ya, Gunakan
                            </button>
                        </div>
                    </div>
                </div>
            ) : null}
        </div>
    );
}
