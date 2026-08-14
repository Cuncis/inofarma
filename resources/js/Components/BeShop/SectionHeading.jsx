import { Link } from '@inertiajs/react';

/**
 * Serif section label with the signature 2px rule underneath and an optional
 * trailing link.
 *
 * @param {{ title: string, action?: string, actionHref?: string, className?: string }} props
 */
export default function SectionHeading({ title, action, actionHref = '#', className = '' }) {
    return (
        <div
            className={`mb-2.5 flex items-center justify-between border-b-2 border-ink pb-2 ${className}`}
        >
            <span className="font-display text-[15px]">{title}</span>

            {action ? (
                <Link href={actionHref} className="text-xs text-brand">
                    {action}
                </Link>
            ) : null}
        </div>
    );
}
