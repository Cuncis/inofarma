import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import Icon from './Icon';

/**
 * Toast for the one-off `flash.success` / `flash.error` messages a redirect
 * carries back after a write.
 *
 * Keyed off the message text so a second identical action re-shows the toast
 * rather than being swallowed as "no change".
 */
export default function FlashMessage() {
    const { flash } = usePage().props;
    const message = flash?.success ?? flash?.error ?? null;
    const tone = flash?.error ? 'error' : 'success';

    const [visible, setVisible] = useState(Boolean(message));

    useEffect(() => {
        setVisible(Boolean(message));

        if (! message) {
            return;
        }

        const timer = window.setTimeout(() => setVisible(false), 4000);

        return () => window.clearTimeout(timer);
    }, [message]);

    if (! visible || ! message) {
        return null;
    }

    return (
        <div
            role="status"
            aria-live="polite"
            className={`fixed bottom-5 right-5 z-50 flex max-w-sm items-start gap-3 rounded-xl px-4 py-3 shadow-pop ${
                tone === 'error' ? 'bg-danger text-white' : 'bg-admin-heading text-white'
            }`}
        >
            <Icon
                name={tone === 'error' ? 'solar:danger-triangle-broken' : 'solar:check-circle-broken'}
                size={19}
                className="mt-px shrink-0"
            />

            <p className="flex-1 text-[13px] leading-relaxed">{message}</p>

            <button
                type="button"
                onClick={() => setVisible(false)}
                aria-label="Tutup notifikasi"
                className="shrink-0 opacity-70 hover:opacity-100"
            >
                <Icon name="solar:close-circle-broken" size={17} />
            </button>
        </div>
    );
}
