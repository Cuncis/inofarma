import { useCallback, useEffect, useState } from 'react';

const STORAGE_KEY = 'inofarma-admin-theme';

/**
 * Read the stored preference, falling back to the OS setting.
 *
 * @returns {boolean}
 */
function initialPreference() {
    if (typeof window === 'undefined') {
        return false;
    }

    const stored = window.localStorage.getItem(STORAGE_KEY);

    if (stored) {
        return stored === 'dark';
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

/**
 * Dark-mode switch for the admin.
 *
 * Toggles the `dark` class on `<html>`, which is what Tailwind's `dark:`
 * variants key off, and remembers the choice across visits.
 *
 * @returns {[boolean, (value: boolean) => void]}
 */
export default function useDarkMode() {
    const [dark, setDark] = useState(initialPreference);

    useEffect(() => {
        document.documentElement.classList.toggle('dark', dark);
        window.localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light');
    }, [dark]);

    return [dark, useCallback((value) => setDark(Boolean(value)), [])];
}
