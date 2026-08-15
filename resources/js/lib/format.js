/**
 * Formatting helpers shared by the storefront and the admin.
 */

/**
 * Format a rupiah amount the way Indonesian storefronts display it.
 *
 * @param {number} amount
 * @returns {string}
 */
export function money(amount) {
    return `Rp ${Math.round(amount).toLocaleString('id-ID')}`;
}

/**
 * Format a rupiah amount compactly, for dashboard tiles where the full figure
 * would not fit — 1250000 becomes "Rp 1,25 jt".
 *
 * @param {number} amount
 * @returns {string}
 */
export function moneyShort(amount) {
    const units = [
        { limit: 1_000_000_000_000, suffix: ' T', divisor: 1_000_000_000_000 },
        { limit: 1_000_000_000, suffix: ' M', divisor: 1_000_000_000 },
        { limit: 1_000_000, suffix: ' jt', divisor: 1_000_000 },
        { limit: 1_000, suffix: ' rb', divisor: 1_000 },
    ];

    for (const unit of units) {
        if (Math.abs(amount) >= unit.limit) {
            const value = amount / unit.divisor;

            return `Rp ${value.toLocaleString('id-ID', { maximumFractionDigits: 2 })}${unit.suffix}`;
        }
    }

    return money(amount);
}

/**
 * Format a plain count with Indonesian thousand separators.
 *
 * @param {number} value
 * @returns {string}
 */
export function count(value) {
    return value.toLocaleString('id-ID');
}
