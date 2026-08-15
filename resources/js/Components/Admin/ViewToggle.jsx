import Icon from './Icon';

const views = [
    { value: 'daftar', label: 'Daftar', icon: 'solar:list-broken' },
    { value: 'grid', label: 'Grid', icon: 'solar:widget-4-broken' },
];

/**
 * List/grid switch for a collection screen.
 *
 * @param {{ value: string, onChange: (value: string) => void }} props
 */
export default function ViewToggle({ value, onChange }) {
    return (
        <div
            role="group"
            aria-label="Tampilan"
            className="inline-flex shrink-0 rounded-lg border border-admin-border p-0.5 dark:border-admin-dark-border"
        >
            {views.map((view) => {
                const selected = view.value === value;

                return (
                    <button
                        key={view.value}
                        type="button"
                        onClick={() => onChange(view.value)}
                        aria-pressed={selected}
                        title={view.label}
                        className={`flex h-8 items-center gap-1.5 rounded-md px-2.5 text-xs font-semibold transition-colors ${
                            selected
                                ? 'bg-brand text-white dark:bg-brand-lift'
                                : 'text-admin-muted hover:bg-admin-hover dark:hover:bg-admin-dark-hover'
                        }`}
                    >
                        <Icon name={view.icon} size={15} />
                        <span className="hidden sm:inline">{view.label}</span>
                    </button>
                );
            })}
        </div>
    );
}
