import { usePage } from '@inertiajs/react';

/**
 * The signed-in admin, shared from the session by `HandleInertiaRequests`.
 *
 * Falls back to a placeholder so screens still read correctly in the brief
 * window before the guard redirects an anonymous visitor to the login page.
 *
 * @returns {{ name: string, email: string, signedIn: boolean }}
 */
export default function useAdminUser() {
    const { adminUser } = usePage().props;

    return {
        name: adminUser?.name || 'Admin Inofarma',
        email: adminUser?.email || 'admin@inofarma.co.id',
        signedIn: Boolean(adminUser),
    };
}
