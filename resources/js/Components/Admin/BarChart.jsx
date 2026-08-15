/**
 * Inline SVG bar chart.
 *
 * The source theme drew its charts with ApexCharts; this renders the same shape
 * with plain SVG so the admin carries no charting dependency. Bars are laid out
 * in a fixed viewBox and scaled by CSS, so the chart is responsive without
 * measuring the DOM.
 *
 * @param {{
 *   series: { label: string, value: number }[],
 *   height?: number,
 *   className?: string,
 * }} props
 */
export default function BarChart({ series, height = 180, className = '' }) {
    const max = Math.max(...series.map((point) => point.value), 1);
    const gap = 8;
    const barWidth = 40;
    const width = series.length * (barWidth + gap) - gap;

    return (
        <div className={className}>
            <svg
                viewBox={`0 0 ${width} ${height}`}
                preserveAspectRatio="none"
                className="w-full"
                style={{ height }}
                role="img"
                aria-label="Grafik pendapatan mingguan"
            >
                {series.map((point, index) => {
                    const barHeight = Math.max((point.value / max) * (height - 28), 2);
                    const x = index * (barWidth + gap);

                    return (
                        <g key={point.label}>
                            <rect
                                x={x}
                                y={height - 24 - barHeight}
                                width={barWidth}
                                height={barHeight}
                                rx="4"
                                className="fill-brand"
                            />
                            <text
                                x={x + barWidth / 2}
                                y={height - 8}
                                textAnchor="middle"
                                className="fill-admin-muted text-[11px] dark:fill-admin-dark-muted"
                            >
                                {point.label}
                            </text>
                        </g>
                    );
                })}
            </svg>
        </div>
    );
}
