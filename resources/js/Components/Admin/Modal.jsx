import { useEffect, useRef } from 'react';
import Icon from './Icon';

/**
 * A dialog that holds a form, not just a confirm/cancel choice.
 *
 * Same shell as `ConfirmDialog` (backdrop click and Escape both close it,
 * focus moves in on open) but the body is whatever the caller renders —
 * built for the stock adjustment and stock receipt forms, which need real
 * fields rather than a yes/no.
 *
 * @param {{
 *   open: boolean,
 *   title: string,
 *   onClose: () => void,
 *   children: import('react').ReactNode,
 * }} props
 */
export default function Modal({ open, title, onClose, children }) {
    const panel = useRef(null);

    useEffect(() => {
        if (! open) {
            return;
        }

        panel.current?.focus();

        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        document.addEventListener('keydown', onKeyDown);

        return () => document.removeEventListener('keydown', onKeyDown);
    }, [open, onClose]);

    if (! open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div onClick={onClose} aria-hidden="true" className="absolute inset-0 bg-admin-heading/50" />

            <div
                ref={panel}
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-title"
                tabIndex={-1}
                className="relative w-full max-w-md rounded-xl border border-admin-border bg-admin-card p-6 shadow-pop focus:outline-none dark:border-admin-dark-border dark:bg-admin-dark-card"
            >
                <div className="mb-4 flex items-center justify-between">
                    <h2
                        id="modal-title"
                        className="text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading"
                    >
                        {title}
                    </h2>

                    <button
                        type="button"
                        onClick={onClose}
                        aria-label="Tutup"
                        className="flex h-8 w-8 items-center justify-center rounded-md text-admin-muted hover:bg-admin-hover dark:text-admin-dark-muted dark:hover:bg-admin-dark-hover"
                    >
                        <Icon name="solar:close-circle-broken" size={18} />
                    </button>
                </div>

                {children}
            </div>
        </div>
    );
}
