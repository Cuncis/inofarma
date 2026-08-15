/**
 * Segmented period control.
 *
 * Sits in the page's filter row rather than inside a chart card, so everything
 * it scopes — the revenue chart and the revenue tile — moves together instead of
 * each panel carrying its own time range.
 *
 * @param {{
 *   value: string,
 *   onChange: (value: string) => void,
 *   options: { value: string, label: string }[],
 *   label?: string,
 * }} props
 */
export default function PeriodFilter({ value, onChange, options, label = 'Periode' }) {
    return (
        <div
            role="group"
            aria-label={label}
            className="inline-flex rounded-lg border border-admin-border bg-admin-card p-0.5 dark:border-admin-dark-border dark:bg-admin-dark-card"
        >
            {options.map((option) => {
                const selected = option.value === value;

                return (
                    <button
                        key={option.value}
                        type="button"
                        onClick={() => onChange(option.value)}
                        aria-pressed={selected}
                        className={`rounded-md px-3 py-1.5 text-xs font-semibold transition-colors ${
                            selected
                                ? 'bg-brand text-white dark:bg-brand-lift'
                                : 'text-admin-body hover:bg-admin-hover dark:text-admin-dark-body dark:hover:bg-admin-dark-hover'
                        }`}
                    >
                        {option.label}
                    </button>
                );
            })}
        </div>
    );
}
