import { Link } from '@inertiajs/react';
import Icon from './Icon';

/**
 * The view / edit / delete trio that ends most admin table rows.
 *
 * `onDelete` is optional — without it the delete control is omitted rather than
 * rendered inert.
 *
 * @param {{ viewHref?: string, editHref?: string, onDelete?: () => void, label: string }} props
 */
export default function RowActions({ viewHref, editHref, onDelete, label }) {
    const base =
        'flex h-8 w-8 items-center justify-center rounded-md transition-colors hover:bg-admin-hover dark:hover:bg-admin-dark-hover';

    return (
        <div className="flex items-center justify-end gap-1">
            {viewHref ? (
                <Link href={viewHref} aria-label={`Lihat ${label}`} className={`${base} text-info-deep`}>
                    <Icon name="solar:eye-broken" size={17} />
                </Link>
            ) : null}

            {editHref ? (
                <Link href={editHref} aria-label={`Ubah ${label}`} className={`${base} text-brand`}>
                    <Icon name="solar:pen-2-broken" size={17} />
                </Link>
            ) : null}

            {onDelete ? (
                <button
                    type="button"
                    onClick={onDelete}
                    aria-label={`Hapus ${label}`}
                    className={`${base} text-danger`}
                >
                    <Icon name="solar:trash-bin-minimalistic-2-broken" size={17} />
                </button>
            ) : null}
        </div>
    );
}
