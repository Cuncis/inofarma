import { useEffect, useRef } from 'react';
import Button from './Button';
import Icon from './Icon';

/**
 * Confirmation for a destructive action.
 *
 * Deleting is irreversible in the store, so it never fires straight off a row
 * button. Escape and the backdrop both cancel; focus moves to the dialog on
 * open so the keyboard path works.
 *
 * @param {{
 *   open: boolean,
 *   title: string,
 *   body: string,
 *   confirmLabel?: string,
 *   processing?: boolean,
 *   onConfirm: () => void,
 *   onCancel: () => void,
 * }} props
 */
export default function ConfirmDialog({
    open,
    title,
    body,
    confirmLabel = 'Hapus',
    processing = false,
    onConfirm,
    onCancel,
}) {
    const panel = useRef(null);

    useEffect(() => {
        if (! open) {
            return;
        }

        panel.current?.focus();

        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                onCancel();
            }
        };

        document.addEventListener('keydown', onKeyDown);

        return () => document.removeEventListener('keydown', onKeyDown);
    }, [open, onCancel]);

    if (! open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div
                onClick={onCancel}
                aria-hidden="true"
                className="absolute inset-0 bg-admin-heading/50"
            />

            <div
                ref={panel}
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="confirm-title"
                aria-describedby="confirm-body"
                tabIndex={-1}
                className="relative w-full max-w-sm rounded-xl border border-admin-border bg-admin-card p-6 shadow-pop focus:outline-none dark:border-admin-dark-border dark:bg-admin-dark-card"
            >
                <span className="mb-4 flex h-11 w-11 items-center justify-center rounded-full bg-[#fdecec] text-danger dark:bg-danger/20">
                    <Icon name="solar:danger-triangle-broken" size={24} />
                </span>

                <h2
                    id="confirm-title"
                    className="text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading"
                >
                    {title}
                </h2>

                <p
                    id="confirm-body"
                    className="mt-1.5 text-[13px] leading-relaxed text-admin-body dark:text-admin-dark-body"
                >
                    {body}
                </p>

                <div className="mt-6 flex justify-end gap-2">
                    <Button variant="outline" size="sm" onClick={onCancel} disabled={processing}>
                        Batal
                    </Button>

                    <Button variant="danger" size="sm" onClick={onConfirm} disabled={processing}>
                        {processing ? 'Menghapus…' : confirmLabel}
                    </Button>
                </div>
            </div>
        </div>
    );
}
