import { usePage } from '@inertiajs/react';

/**
 * The prototype session user set by the sign-in screen.
 *
 * Falls back to the design's placeholder shopper so the screens still read
 * correctly when nobody has signed in yet.
 *
 * @returns {{ name: string, email: string, signedIn: boolean }}
 */
export default function useBeShopUser() {
    const { beshopUser } = usePage().props;

    return {
        name: beshopUser?.name || 'Kirana Wijaya',
        email: beshopUser?.email || 'kirana.wijaya@mail.com',
        signedIn: Boolean(beshopUser),
    };
}
