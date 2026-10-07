import { useEffect, useState } from 'react';

const STORAGE_KEY = 'inofarma.recentlyViewed';
const MAX_STORED = 12;

/**
 * @returns {string[]} Product ids, most recent first.
 */
function readIds() {
    try {
        const parsed = JSON.parse(window.localStorage.getItem(STORAGE_KEY) ?? '[]');

        return Array.isArray(parsed) ? parsed.filter((id) => typeof id === 'string') : [];
    } catch {
        return [];
    }
}

/**
 * Remember that the shopper opened this product. Stored in the browser only
 * (no account needed), newest first, de-duplicated and capped. Storage can be
 * blocked (private mode), in which case this quietly does nothing.
 *
 * @param {string|undefined} productId
 */
export function useTrackProductView(productId) {
    useEffect(() => {
        if (! productId) {
            return;
        }

        try {
            const ids = [productId, ...readIds().filter((id) => id !== productId)].slice(0, MAX_STORED);

            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
        } catch {
            // Storage unavailable; the section just stays empty.
        }
    }, [productId]);
}

/**
 * Ids of recently viewed products, newest first, read once on mount.
 *
 * @param {string} [excludeId]
 * @returns {string[]}
 */
export default function useRecentlyViewed(excludeId) {
    const [ids, setIds] = useState([]);

    useEffect(() => {
        setIds(readIds().filter((id) => id !== excludeId));
    }, [excludeId]);

    return ids;
}
