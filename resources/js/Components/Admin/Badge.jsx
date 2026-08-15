/**
 * Soft status pill.
 *
 * Every tone pairs a light tint with a dark-enough foreground to stay readable —
 * the raw palette green and orange are not usable as text on a light fill.
 *
 * @type {Record<string, string>}
 */
const tones = {
    brand: 'bg-blush text-brand dark:bg-brand/20 dark:text-white',
    success: 'bg-[#e8f9e9] text-success-deep dark:bg-success/20 dark:text-success',
    warning: 'bg-[#fff1e3] text-warning-deep dark:bg-warning/20 dark:text-warning',
    danger: 'bg-[#fdecec] text-danger-deep dark:bg-danger/20 dark:text-danger',
    info: 'bg-[#e6f7f5] text-info-deep dark:bg-info/20 dark:text-info',
    neutral:
        'bg-admin-hover text-admin-body dark:bg-admin-dark-hover dark:text-admin-dark-body',
};

/**
 * @param {{
 *   children: import('react').ReactNode,
 *   tone?: keyof typeof tones,
 *   className?: string,
 * }} props
 */
export default function Badge({ children, tone = 'neutral', className = '' }) {
    return (
        <span
            className={`inline-flex items-center rounded-md px-2 py-1 text-[11px] font-semibold ${tones[tone] ?? tones.neutral} ${className}`}
        >
            {children}
        </span>
    );
}
