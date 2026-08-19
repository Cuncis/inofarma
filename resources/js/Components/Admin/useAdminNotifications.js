import { usePage } from '@inertiajs/react';

/**
 * The signed-in admin's notification bell contents (Fase 8), shared from
 * `HandleInertiaRequests` — null for a guest, same convention as
 * `useAdminUser`.
 *
 * @returns {{ unreadCount: number, items: object[] }}
 */
export default function useAdminNotifications() {
    const { adminNotifications } = usePage().props;

    return {
        unreadCount: adminNotifications?.unreadCount ?? 0,
        items: adminNotifications?.items ?? [],
    };
}
