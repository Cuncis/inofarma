import { usePage } from '@inertiajs/react';

/**
 * The shopper's cart item count, shared from `HandleInertiaRequests` — same
 * source `TabBar`'s own badge already reads, just exposed for the header
 * cart icon too.
 *
 * @returns {number}
 */
export default function useCartCount() {
    const { cartCount } = usePage().props;

    return cartCount ?? 0;
}
