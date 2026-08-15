import { useId, useState } from 'react';
import { moneyShort } from '@/lib/format';

/**
 * Round a maximum up to a readable axis top, so ticks land on clean numbers.
 *
 * @param {number} value
 * @returns {number}
 */
function niceMax(value) {
    if (value <= 0) {
        return 1;
    }

    const magnitude = 10 ** Math.floor(Math.log10(value));
    const steps = [1, 1.25, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10];

    return magnitude * (steps.find((step) => magnitude * step >= value) ?? 10);
}

/**
 * Column chart for a single measure over time.
 *
 * Laid out with real elements rather than a scaled SVG: the previous version
 * stretched a fixed viewBox to the card width with `preserveAspectRatio="none"`,
 * which distorted the labels and the rounded corners. Here the bars are flex
 * children, so nothing is scaled non-uniformly and the axis text stays crisp.
 *
 * One series, so no legend — the card title names the measure. Only the peak is
 * direct-labelled; the axis and the per-bar tooltip carry the rest, and the
 * table view is the accessible twin.
 *
 * @param {{
 *   series: { label: string, value: number }[],
 *   height?: number,
 *   format?: (value: number) => string,
 *   className?: string,
 * }} props
 */
export default function BarChart({
    series,
    height = 190,
    format = moneyShort,
    className = '',
}) {
    const [active, setActive] = useState(null);
    const [showTable, setShowTable] = useState(false);
    const tableId = useId();

    const top = niceMax(Math.max(...series.map((point) => point.value), 1));
    const ticks = [1, 0.75, 0.5, 0.25, 0].map((fraction) => fraction * top);
    const peak = series.reduce(
        (best, point) => (point.value > best.value ? point : best),
        series[0],
    );

    return (
        <figure className={className}>
            <div className="flex gap-3">
                <div
                    className="flex shrink-0 flex-col justify-between pb-px text-right text-[10px] tabular-nums text-admin-muted dark:text-admin-dark-muted"
                    style={{ height }}
                    aria-hidden="true"
                >
                    {ticks.map((tick) => (
                        <span key={tick} className="leading-none">
                            {format(tick)}
                        </span>
                    ))}
                </div>

                <div className="min-w-0 flex-1">
                    <div className="relative" style={{ height }}>
                        {ticks.map((tick) => (
                            <span
                                key={tick}
                                aria-hidden="true"
                                style={{ bottom: `${(tick / top) * 100}%` }}
                                className="absolute inset-x-0 h-px bg-admin-border dark:bg-admin-dark-border"
                            />
                        ))}

                        <div className="absolute inset-0 flex items-end gap-1.5">
                            {series.map((point) => {
                                const share = (point.value / top) * 100;
                                const isPeak = point.label === peak.label;
                                const isActive = active === point.label;

                                return (
                                    <button
                                        key={point.label}
                                        type="button"
                                        onMouseEnter={() => setActive(point.label)}
                                        onMouseLeave={() => setActive(null)}
                                        onFocus={() => setActive(point.label)}
                                        onBlur={() => setActive(null)}
                                        aria-label={`${point.label}: ${format(point.value)}`}
                                        className="group relative flex h-full flex-1 items-end justify-center rounded-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-brand"
                                    >
                                        <span
                                            style={{ height: `${Math.max(share, 1)}%` }}
                                            className={`w-full max-w-6 rounded-t transition-opacity ${
                                                isActive || isPeak
                                                    ? 'bg-brand dark:bg-brand-lift'
                                                    : 'bg-brand/70 dark:bg-brand-lift/70'
                                            }`}
                                        />

                                        {isPeak && ! isActive ? (
                                            <span
                                                style={{ bottom: `calc(${share}% + 6px)` }}
                                                className="pointer-events-none absolute whitespace-nowrap text-[10px] font-semibold tabular-nums text-admin-heading dark:text-admin-dark-heading"
                                            >
                                                {format(point.value)}
                                            </span>
                                        ) : null}

                                        {isActive ? (
                                            <span
                                                style={{ bottom: `calc(${share}% + 6px)` }}
                                                role="tooltip"
                                                className="pointer-events-none absolute z-10 whitespace-nowrap rounded-md bg-admin-heading px-2 py-1 text-[10px] font-semibold tabular-nums text-white shadow-pop dark:bg-admin-dark-border"
                                            >
                                                {point.label} · {format(point.value)}
                                            </span>
                                        ) : null}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    <div className="mt-2 flex gap-1.5">
                        {series.map((point) => (
                            <span
                                key={point.label}
                                className={`flex-1 text-center text-[11px] ${
                                    active === point.label
                                        ? 'font-semibold text-admin-heading dark:text-admin-dark-heading'
                                        : 'text-admin-muted dark:text-admin-dark-muted'
                                }`}
                            >
                                {point.label}
                            </span>
                        ))}
                    </div>
                </div>
            </div>

            <div className="mt-3 flex justify-end">
                <button
                    type="button"
                    onClick={() => setShowTable((current) => ! current)}
                    aria-expanded={showTable}
                    aria-controls={tableId}
                    className="text-[11px] font-medium text-brand hover:underline dark:text-brand-lift"
                >
                    {showTable ? 'Sembunyikan tabel' : 'Lihat tabel'}
                </button>
            </div>

            {showTable ? (
                <div id={tableId} className="mt-2 overflow-x-auto">
                    <table className="w-full border-collapse text-left">
                        <thead>
                            <tr className="border-b border-admin-border dark:border-admin-dark-border">
                                <th className="py-2 text-[11px] font-bold uppercase tracking-wider text-admin-muted dark:text-admin-dark-muted">
                                    Periode
                                </th>
                                <th className="py-2 text-right text-[11px] font-bold uppercase tracking-wider text-admin-muted dark:text-admin-dark-muted">
                                    Nilai
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            {series.map((point) => (
                                <tr
                                    key={point.label}
                                    className="border-b border-admin-border last:border-0 dark:border-admin-dark-border"
                                >
                                    <td className="py-1.5 text-[12px] text-admin-body dark:text-admin-dark-body">
                                        {point.label}
                                    </td>
                                    <td className="py-1.5 text-right text-[12px] tabular-nums text-admin-heading dark:text-admin-dark-heading">
                                        {format(point.value)}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            ) : null}
        </figure>
    );
}
