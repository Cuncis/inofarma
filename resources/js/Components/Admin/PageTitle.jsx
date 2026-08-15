import { Link } from '@inertiajs/react';
import Icon from './Icon';

/**
 * Page heading with breadcrumb trail and an optional action slot.
 *
 * @param {{
 *   heading: string,
 *   breadcrumb?: { label: string, href?: string }[],
 *   actions?: import('react').ReactNode,
 * }} props
 */
export default function PageTitle({ heading, breadcrumb = [], actions }) {
    return (
        <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 className="text-xl font-semibold text-admin-heading dark:text-admin-dark-heading">
                    {heading}
                </h1>

                {breadcrumb.length ? (
                    <nav aria-label="Breadcrumb" className="mt-1.5">
                        <ol className="flex flex-wrap items-center gap-1 text-xs text-admin-muted dark:text-admin-dark-muted">
                            {breadcrumb.map((crumb, index) => (
                                <li key={crumb.label} className="flex items-center gap-1">
                                    {index > 0 ? (
                                        <Icon
                                            name="solar:alt-arrow-right-bold-duotone"
                                            size={12}
                                            className="opacity-60"
                                        />
                                    ) : null}

                                    {crumb.href ? (
                                        <Link href={crumb.href} className="hover:text-brand">
                                            {crumb.label}
                                        </Link>
                                    ) : (
                                        <span
                                            className="text-admin-body dark:text-admin-dark-body"
                                            aria-current="page"
                                        >
                                            {crumb.label}
                                        </span>
                                    )}
                                </li>
                            ))}
                        </ol>
                    </nav>
                ) : null}
            </div>

            {actions ? <div className="flex items-center gap-2">{actions}</div> : null}
        </div>
    );
}
