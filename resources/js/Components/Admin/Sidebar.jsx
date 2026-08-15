import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import Icon from './Icon';
import { navSections } from './nav';

/**
 * Is this path the one currently being viewed?
 *
 * `/admin` has to match exactly, otherwise it would light up on every admin
 * page; deeper paths also match their own sub-routes.
 *
 * @param {string} current
 * @param {string} href
 * @returns {boolean}
 */
function isActive(current, href) {
    if (! href) {
        return false;
    }

    return href === '/admin' ? current === '/admin' : current.startsWith(href);
}

/**
 * @param {{ item: import('./nav').NavItem, current: string, collapsed: boolean }} props
 */
function NavItem({ item, current, collapsed }) {
    const childActive = item.children?.some((child) => isActive(current, child.href)) ?? false;
    const [open, setOpen] = useState(childActive);

    // Following a link into a different branch should close this one, and
    // landing inside it should open it.
    useEffect(() => setOpen(childActive), [childActive]);

    const active = isActive(current, item.href) || childActive;

    const base =
        'group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-[13.5px] transition-colors';
    const tone = active
        ? 'bg-brand text-white'
        : 'text-admin-body hover:bg-admin-hover hover:text-admin-heading dark:text-admin-dark-body dark:hover:bg-admin-dark-hover dark:hover:text-admin-dark-heading';

    if (! item.children) {
        return (
            <li>
                <Link href={item.href} className={`${base} ${tone}`} title={collapsed ? item.label : undefined}>
                    <Icon name={item.icon} size={20} className="shrink-0" />
                    {! collapsed ? <span className="truncate">{item.label}</span> : null}
                </Link>
            </li>
        );
    }

    return (
        <li>
            <button
                type="button"
                onClick={() => setOpen((current) => ! current)}
                aria-expanded={open}
                className={`${base} ${tone}`}
                title={collapsed ? item.label : undefined}
            >
                <Icon name={item.icon} size={20} className="shrink-0" />

                {! collapsed ? (
                    <>
                        <span className="flex-1 truncate text-left">{item.label}</span>
                        <Icon
                            name="solar:alt-arrow-down-bold-duotone"
                            size={14}
                            className={`shrink-0 transition-transform ${open ? 'rotate-180' : ''}`}
                        />
                    </>
                ) : null}
            </button>

            {open && ! collapsed ? (
                <ul className="mt-1 space-y-0.5 border-l border-admin-border pl-4 dark:border-admin-dark-border">
                    {item.children.map((child) => (
                        <li key={child.href}>
                            <Link
                                href={child.href}
                                className={`block rounded-md px-3 py-2 text-[13px] transition-colors ${
                                    isActive(current, child.href)
                                        ? 'font-semibold text-brand dark:text-white'
                                        : 'text-admin-body hover:text-admin-heading dark:text-admin-dark-body dark:hover:text-admin-dark-heading'
                                }`}
                            >
                                {child.label}
                            </Link>
                        </li>
                    ))}
                </ul>
            ) : null}
        </li>
    );
}

/**
 * Fixed sidebar: brand mark, scrolling menu, and a rail-width collapsed mode.
 *
 * On screens below `lg` it slides in over the page and `open`/`onClose` drive it;
 * from `lg` up it is always visible and `collapsed` switches it to the icon rail.
 *
 * @param {{ open: boolean, collapsed: boolean, onClose: () => void }} props
 */
export default function Sidebar({ open, collapsed, onClose }) {
    const { url } = usePage();
    const current = url.split('?')[0];

    return (
        <>
            {open ? (
                <div
                    onClick={onClose}
                    className="fixed inset-0 z-30 bg-admin-heading/40 lg:hidden"
                    aria-hidden="true"
                />
            ) : null}

            <aside
                className={`fixed inset-y-0 left-0 z-40 flex flex-col border-r border-admin-border bg-admin-nav transition-[width,transform] duration-300 dark:border-admin-dark-border dark:bg-admin-dark-nav ${
                    collapsed ? 'w-sidebar-sm' : 'w-sidebar'
                } ${open ? 'translate-x-0' : '-translate-x-full'} lg:translate-x-0`}
            >
                <div
                    className={`flex h-topbar shrink-0 items-center gap-2.5 px-5 ${
                        collapsed ? 'justify-center px-0' : ''
                    }`}
                >
                    <Link href="/admin" className="flex items-center gap-2.5">
                        <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand text-white">
                            <Icon name="solar:pill-bold-duotone" size={22} />
                        </span>

                        {! collapsed ? (
                            <span className="text-lg font-bold tracking-tight text-admin-heading dark:text-admin-dark-heading">
                                Inofarma
                            </span>
                        ) : null}
                    </Link>
                </div>

                <nav className="flex-1 overflow-y-auto px-3 pb-6">
                    {navSections.map((section) => (
                        <div key={section.title} className="mb-4">
                            {! collapsed ? (
                                <p className="px-3 pb-2 pt-3 text-[10px] font-bold uppercase tracking-[1.5px] text-admin-muted dark:text-admin-dark-muted">
                                    {section.title}
                                </p>
                            ) : (
                                <div className="my-3 border-t border-admin-border dark:border-admin-dark-border" />
                            )}

                            <ul className="space-y-0.5">
                                {section.items.map((item) => (
                                    <NavItem
                                        key={item.label}
                                        item={item}
                                        current={current}
                                        collapsed={collapsed}
                                    />
                                ))}
                            </ul>
                        </div>
                    ))}
                </nav>
            </aside>
        </>
    );
}
