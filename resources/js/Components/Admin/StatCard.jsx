import Icon from './Icon';

/**
 * Dashboard summary tile: icon, figure, and period-over-period movement.
 *
 * @param {{
 *   label: string,
 *   value: string,
 *   icon: string,
 *   change?: string,
 *   up?: boolean,
 *   period?: string,
 * }} props
 */
export default function StatCard({ label, value, icon, change, up = true, period }) {
    return (
        <div className="rounded-xl border border-admin-border bg-admin-card shadow-card dark:border-admin-dark-border dark:bg-admin-dark-card">
            <div className="flex items-start justify-between gap-3 p-5">
                <span className="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blush text-brand dark:bg-brand/20 dark:text-white">
                    <Icon name={icon} size={26} />
                </span>

                <div className="min-w-0 text-right">
                    <p className="truncate text-xs text-admin-muted dark:text-admin-dark-muted">
                        {label}
                    </p>
                    <p className="mt-1 truncate text-xl font-semibold text-admin-heading dark:text-admin-dark-heading">
                        {value}
                    </p>
                </div>
            </div>

            {change ? (
                <div className="flex items-center justify-between border-t border-admin-border px-5 py-2.5 dark:border-admin-dark-border">
                    <p className="flex items-center gap-1 text-xs">
                        <Icon
                            name={up ? 'solar:arrow-up-bold-duotone' : 'solar:arrow-down-bold-duotone'}
                            size={14}
                            className={up ? 'text-success-deep' : 'text-danger'}
                        />
                        <span className={up ? 'text-success-deep' : 'text-danger'}>{change}</span>
                        <span className="ml-1 text-admin-muted dark:text-admin-dark-muted">
                            {period}
                        </span>
                    </p>
                </div>
            ) : null}
        </div>
    );
}
