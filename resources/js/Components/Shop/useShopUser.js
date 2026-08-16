import { usePage } from '@inertiajs/react';

/**
 * The signed-in customer, shared from `HandleInertiaRequests` via the
 * `customer` guard (Fase 3.3).
 *
 * Falls back to the design's placeholder shopper so the screens still read
 * correctly when nobody has signed in yet.
 *
 * @returns {{ name: string, email: string, signedIn: boolean }}
 */
export default function useShopUser() {
    const { shopUser } = usePage().props;

    return {
        name: shopUser?.name || 'Kirana Wijaya',
        email: shopUser?.email || 'kirana.wijaya@mail.com',
        signedIn: Boolean(shopUser),
    };
}
