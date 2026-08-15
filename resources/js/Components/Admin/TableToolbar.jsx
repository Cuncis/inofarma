import Icon from './Icon';
import { Select } from './Form';

/**
 * Search box, optional filter select, and an action slot — the header strip
 * shared by every admin list screen.
 *
 * @param {{
 *   search: string,
 *   onSearch: (value: string) => void,
 *   placeholder?: string,
 *   filter?: { value: string, onChange: (value: string) => void, options: string[] },
 *   children?: import('react').ReactNode,
 * }} props
 */
export default function TableToolbar({
    search,
    onSearch,
    placeholder = 'Cari...',
    filter,
    children,
}) {
    return (
        <div className="flex flex-wrap items-center gap-3 border-b border-admin-border px-5 py-4 dark:border-admin-dark-border">
            <div className="relative min-w-[200px] flex-1">
                <Icon
                    name="solar:magnifer-linear"
                    size={17}
                    className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-admin-muted"
                />
                <input
                    type="search"
                    value={search}
                    onChange={(event) => onSearch(event.target.value)}
                    placeholder={placeholder}
                    className="h-10 w-full rounded-lg border border-admin-border bg-admin-card pl-10 pr-3 text-[13px] text-admin-body placeholder:text-admin-muted focus:border-brand focus:outline-none focus:ring-0 dark:border-admin-dark-border dark:bg-admin-dark-card dark:text-admin-dark-body"
                />
            </div>

            {filter ? (
                <Select
                    value={filter.value}
                    onChange={(event) => filter.onChange(event.target.value)}
                    options={filter.options}
                    className="w-auto min-w-[150px]"
                />
            ) : null}

            {children}
        </div>
    );
}
