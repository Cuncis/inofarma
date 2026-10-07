import { useState } from 'react';

const STORAGE_KEY = 'inofarma_location_consent';

/**
 * PDP (UU 27/2022) treats location a separate category of personal data from
 * the rest of an account — ROADMAP.md Fase 9.2 asks for a consent distinct
 * from the one ticked at registration, not folded into it. Persisted in
 * `localStorage` rather than the server: this is purely "may this browser
 * ask the device for its coordinates," decided before any request is made,
 * so there is nothing to send until the answer is already yes.
 *
 * Shared by every screen that calls `navigator.geolocation` —
 * `Shop/OurBranches.jsx` and `Shop/AddNewAddress.jsx` — so agreeing once
 * covers both instead of asking again per screen.
 */
export default function useLocationConsent() {
    const [consented, setConsented] = useState(
        () => typeof window !== 'undefined' && window.localStorage.getItem(STORAGE_KEY) === '1',
    );

    const consent = () => {
        window.localStorage.setItem(STORAGE_KEY, '1');
        setConsented(true);
    };

    return { consented, consent };
}
