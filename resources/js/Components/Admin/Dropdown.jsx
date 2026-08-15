import { useEffect, useRef, useState } from 'react';

/**
 * Click-outside dropdown.
 *
 * Replaces the source theme's Bootstrap `data-bs-toggle="dropdown"` behaviour
 * with React state, so no imperative plugin has to reach into the DOM.
 *
 * @param {{
 *   trigger: (props: { open: boolean }) => import('react').ReactNode,
 *   children: import('react').ReactNode,
 *   align?: 'left'|'right',
 *   width?: string,
 *   label: string,
 * }} props
 */
export default function Dropdown({ trigger, children, align = 'right', width = 'w-72', label }) {
    const [open, setOpen] = useState(false);
    const holder = useRef(null);

    useEffect(() => {
        if (! open) {
            return;
        }

        const onPointerDown = (event) => {
            if (! holder.current?.contains(event.target)) {
                setOpen(false);
            }
        };

        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', onPointerDown);
        document.addEventListener('keydown', onKeyDown);

        return () => {
            document.removeEventListener('mousedown', onPointerDown);
            document.removeEventListener('keydown', onKeyDown);
        };
    }, [open]);

    return (
        <div ref={holder} className="relative">
            <button
                type="button"
                onClick={() => setOpen((current) => ! current)}
                aria-expanded={open}
                aria-haspopup="true"
                aria-label={label}
                className="flex h-10 items-center justify-center rounded-lg px-2 text-admin-body transition-colors hover:bg-admin-hover hover:text-admin-heading dark:text-admin-dark-body dark:hover:bg-admin-dark-hover dark:hover:text-admin-dark-heading"
            >
                {trigger({ open })}
            </button>

            {open ? (
                <div
                    onClick={() => setOpen(false)}
                    className={`absolute top-full z-50 mt-2 ${width} overflow-hidden rounded-xl border border-admin-border bg-admin-card shadow-pop dark:border-admin-dark-border dark:bg-admin-dark-card ${
                        align === 'right' ? 'right-0' : 'left-0'
                    }`}
                >
                    {children}
                </div>
            ) : null}
        </div>
    );
}
