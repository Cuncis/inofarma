import { Link } from '@inertiajs/react';

/**
 * Panel surface used by every admin screen.
 *
 * @param {{
 *   children: import('react').ReactNode,
 *   title?: string,
 *   action?: { label: string, href: string },
 *   bodyClassName?: string,
 *   className?: string,
 * }} props
 */
export default function Card({ children, title, action, bodyClassName = 'p-5', className = '' }) {
    return (
        <div
            className={`rounded-xl border border-admin-border bg-admin-card shadow-card dark:border-admin-dark-border dark:bg-admin-dark-card ${className}`}
        >
            {title || action ? (
                <div className="flex items-center justify-between border-b border-admin-border px-5 py-4 dark:border-admin-dark-border">
                    <h2 className="text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                        {title}
                    </h2>

                    {action ? (
                        <Link
                            href={action.href}
                            className="text-xs font-medium text-brand hover:underline"
                        >
                            {action.label}
                        </Link>
                    ) : null}
                </div>
            ) : null}

            <div className={bodyClassName}>{children}</div>
        </div>
    );
}
