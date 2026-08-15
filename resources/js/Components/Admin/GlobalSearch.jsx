import { useEffect, useMemo, useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import Icon from './Icon';
import { searchAdmin } from './search';

/**
 * Topbar search.
 *
 * Types into a live result list covering products, orders, customers, sellers,
 * invoices and the admin's own pages. Arrow keys move the highlight, Enter opens
 * it, Escape closes; `/` from anywhere on the page focuses the field.
 */
export default function GlobalSearch() {
    const [query, setQuery] = useState('');
    const [open, setOpen] = useState(false);
    const [active, setActive] = useState(0);
    const holder = useRef(null);
    const field = useRef(null);

    const results = useMemo(() => searchAdmin(query), [query]);

    // Reset the highlight whenever the result set changes under it.
    useEffect(() => setActive(0), [query]);

    useEffect(() => {
        const onPointerDown = (event) => {
            if (! holder.current?.contains(event.target)) {
                setOpen(false);
            }
        };

        const onKeyDown = (event) => {
            const typingElsewhere =
                event.target instanceof HTMLElement &&
                ['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName);

            if (event.key === '/' && ! typingElsewhere) {
                event.preventDefault();
                field.current?.focus();
            }
        };

        document.addEventListener('mousedown', onPointerDown);
        document.addEventListener('keydown', onKeyDown);

        return () => {
            document.removeEventListener('mousedown', onPointerDown);
            document.removeEventListener('keydown', onKeyDown);
        };
    }, []);

    const go = (entry) => {
        setOpen(false);
        setQuery('');
        field.current?.blur();
        router.visit(entry.href);
    };

    const onKeyDown = (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
            field.current?.blur();

            return;
        }

        if (! results.length) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActive((current) => (current + 1) % results.length);
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActive((current) => (current - 1 + results.length) % results.length);
        }

        if (event.key === 'Enter') {
            event.preventDefault();
            go(results[active]);
        }
    };

    const showPanel = open && query.trim().length > 0;

    // Results arrive pre-sorted by score, so group headings are emitted the
    // first time each group appears rather than by regrouping the list.
    const seenGroups = new Set();

    return (
        <div ref={holder} className="relative ml-1 hidden md:block">
            <Icon
                name="solar:magnifer-linear"
                size={18}
                className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-admin-muted"
            />

            <input
                ref={field}
                type="search"
                value={query}
                onChange={(event) => {
                    setQuery(event.target.value);
                    setOpen(true);
                }}
                onFocus={() => setOpen(true)}
                onKeyDown={onKeyDown}
                placeholder="Cari produk, pesanan, pelanggan..."
                aria-label="Pencarian global"
                aria-expanded={showPanel}
                role="combobox"
                aria-controls="admin-search-results"
                className="h-10 w-72 rounded-lg border border-admin-border bg-admin-bg pl-10 pr-9 text-[13px] text-admin-body placeholder:text-admin-muted focus:border-brand focus:outline-none focus:ring-0 dark:border-admin-dark-border dark:bg-admin-dark-bg dark:text-admin-dark-body [&::-webkit-search-cancel-button]:hidden"
            />

            {query ? (
                <button
                    type="button"
                    onClick={() => {
                        setQuery('');
                        field.current?.focus();
                    }}
                    aria-label="Hapus pencarian"
                    className="absolute right-2 top-1/2 flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded text-admin-muted hover:bg-admin-hover dark:hover:bg-admin-dark-hover"
                >
                    <Icon name="solar:close-circle-broken" size={16} />
                </button>
            ) : (
                <kbd className="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 rounded border border-admin-border px-1.5 py-0.5 text-[10px] text-admin-muted dark:border-admin-dark-border">
                    /
                </kbd>
            )}

            {showPanel ? (
                <div
                    id="admin-search-results"
                    role="listbox"
                    className="absolute left-0 top-full z-50 mt-2 w-[26rem] overflow-hidden rounded-xl border border-admin-border bg-admin-card shadow-pop dark:border-admin-dark-border dark:bg-admin-dark-card"
                >
                    {results.length === 0 ? (
                        <p className="px-4 py-6 text-center text-[13px] text-admin-muted dark:text-admin-dark-muted">
                            Tidak ada hasil untuk &ldquo;{query.trim()}&rdquo;
                        </p>
                    ) : (
                        <ul className="max-h-96 overflow-y-auto py-1">
                            {results.map((entry, index) => {
                                const firstOfGroup = ! seenGroups.has(entry.group);
                                seenGroups.add(entry.group);

                                return (
                                    <li key={`${entry.group}-${entry.label}-${index}`}>
                                        {firstOfGroup ? (
                                            <p className="px-4 pb-1 pt-2.5 text-[10px] font-bold uppercase tracking-wider text-admin-muted dark:text-admin-dark-muted">
                                                {entry.group}
                                            </p>
                                        ) : null}

                                        <button
                                            type="button"
                                            role="option"
                                            aria-selected={index === active}
                                            onMouseEnter={() => setActive(index)}
                                            onClick={() => go(entry)}
                                            className={`flex w-full items-center gap-3 px-4 py-2.5 text-left transition-colors ${
                                                index === active
                                                    ? 'bg-blush dark:bg-brand/20'
                                                    : 'hover:bg-admin-hover dark:hover:bg-admin-dark-hover'
                                            }`}
                                        >
                                            <Icon
                                                name={entry.icon}
                                                size={18}
                                                className="shrink-0 text-admin-muted"
                                            />

                                            <span className="min-w-0 flex-1">
                                                <span className="block truncate text-[13px] font-medium text-admin-heading dark:text-admin-dark-heading">
                                                    {entry.label}
                                                </span>
                                                {entry.meta ? (
                                                    <span className="block truncate text-xs text-admin-muted dark:text-admin-dark-muted">
                                                        {entry.meta}
                                                    </span>
                                                ) : null}
                                            </span>
                                        </button>
                                    </li>
                                );
                            })}
                        </ul>
                    )}

                    <p className="border-t border-admin-border px-4 py-2 text-[10px] text-admin-muted dark:border-admin-dark-border dark:text-admin-dark-muted">
                        ↑↓ pilih · Enter buka · Esc tutup
                    </p>
                </div>
            ) : null}
        </div>
    );
}
