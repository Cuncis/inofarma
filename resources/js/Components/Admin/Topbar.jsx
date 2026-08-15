import { Link } from '@inertiajs/react';
import Dropdown from './Dropdown';
import GlobalSearch from './GlobalSearch';
import Icon from './Icon';
import useAdminUser from './useAdminUser';
import useDarkMode from './useDarkMode';
import { notifications } from './data';

/**
 * Sticky admin header: sidebar controls, search, theme toggle, notifications
 * and the account menu.
 *
 * @param {{ onToggleSidebar: () => void, onToggleCollapse: () => void }} props
 */
export default function Topbar({ onToggleSidebar, onToggleCollapse }) {
    const [dark, setDark] = useDarkMode();
    const admin = useAdminUser();

    return (
        <header className="sticky top-0 z-30 flex h-topbar shrink-0 items-center gap-2 border-b border-admin-border bg-admin-nav px-4 dark:border-admin-dark-border dark:bg-admin-dark-nav sm:px-6">
            <button
                type="button"
                onClick={onToggleSidebar}
                aria-label="Buka menu"
                className="flex h-10 w-10 items-center justify-center rounded-lg text-admin-body hover:bg-admin-hover dark:text-admin-dark-body dark:hover:bg-admin-dark-hover lg:hidden"
            >
                <Icon name="solar:hamburger-menu-broken" size={24} />
            </button>

            <button
                type="button"
                onClick={onToggleCollapse}
                aria-label="Perkecil menu"
                className="hidden h-10 w-10 items-center justify-center rounded-lg text-admin-body hover:bg-admin-hover dark:text-admin-dark-body dark:hover:bg-admin-dark-hover lg:flex"
            >
                <Icon name="solar:hamburger-menu-broken" size={24} />
            </button>

            <GlobalSearch />

            <div className="ml-auto flex items-center gap-1">
                <button
                    type="button"
                    onClick={() => setDark(! dark)}
                    aria-label={dark ? 'Mode terang' : 'Mode gelap'}
                    className="flex h-10 w-10 items-center justify-center rounded-lg text-admin-body transition-colors hover:bg-admin-hover hover:text-admin-heading dark:text-admin-dark-body dark:hover:bg-admin-dark-hover"
                >
                    <Icon
                        name={dark ? 'solar:sun-bold-duotone' : 'solar:moon-bold-duotone'}
                        size={22}
                    />
                </button>

                <Dropdown
                    label="Notifikasi"
                    width="w-80"
                    trigger={() => (
                        <span className="relative flex h-10 w-10 items-center justify-center">
                            <Icon name="solar:bell-bing-bold-duotone" size={22} />
                            <span className="absolute right-1.5 top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-danger px-1 text-[9px] font-bold text-white">
                                {notifications.length}
                            </span>
                        </span>
                    )}
                >
                    <div className="flex items-center justify-between border-b border-admin-border px-4 py-3 dark:border-admin-dark-border">
                        <h6 className="text-sm font-semibold text-admin-heading dark:text-admin-dark-heading">
                            Notifikasi
                        </h6>
                        <button type="button" className="text-xs text-brand hover:underline">
                            Bersihkan
                        </button>
                    </div>

                    <div className="max-h-72 overflow-y-auto">
                        {notifications.map((item) => (
                            <div
                                key={item.name}
                                className="flex gap-3 border-b border-admin-border px-4 py-3 last:border-0 hover:bg-admin-hover dark:border-admin-dark-border dark:hover:bg-admin-dark-hover"
                            >
                                <img
                                    src={item.avatar}
                                    alt={item.name}
                                    className="h-9 w-9 shrink-0 rounded-full object-cover"
                                />
                                <div className="min-w-0">
                                    <p className="text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                        {item.name}
                                    </p>
                                    <p className="text-xs leading-relaxed text-admin-body dark:text-admin-dark-body">
                                        {item.body}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="p-3">
                        <Link
                            href="/admin/chat"
                            className="block rounded-lg bg-brand px-3 py-2 text-center text-xs font-semibold text-white"
                        >
                            Lihat Semua
                        </Link>
                    </div>
                </Dropdown>

                <Dropdown
                    label="Akun"
                    width="w-56"
                    trigger={() => (
                        <span className="flex items-center gap-2">
                            <img
                                src="/media/images/users/avatar-1.jpg"
                                alt=""
                                className="h-8 w-8 rounded-full object-cover"
                            />
                        </span>
                    )}
                >
                    <p className="border-b border-admin-border px-4 py-3 text-xs font-semibold text-admin-muted dark:border-admin-dark-border dark:text-admin-dark-muted">
                        {admin.name}
                    </p>

                    {[
                        { label: 'Profil', icon: 'solar:user-circle-bold-duotone', href: '/admin/profil' },
                        { label: 'Pesan', icon: 'solar:chat-round-bold-duotone', href: '/admin/chat' },
                        { label: 'Pengaturan', icon: 'solar:settings-bold-duotone', href: '/admin/pengaturan' },
                        { label: 'Bantuan', icon: 'solar:help-bold-duotone', href: '/admin/bantuan' },
                    ].map((item) => (
                        <Link
                            key={item.href}
                            href={item.href}
                            className="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-admin-body hover:bg-admin-hover dark:text-admin-dark-body dark:hover:bg-admin-dark-hover"
                        >
                            <Icon name={item.icon} size={18} className="text-admin-muted" />
                            {item.label}
                        </Link>
                    ))}

                    <div className="border-t border-admin-border dark:border-admin-dark-border">
                        <Link
                            href="/admin/keluar"
                            method="post"
                            as="button"
                            className="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-[13px] font-medium text-danger hover:bg-admin-hover dark:hover:bg-admin-dark-hover"
                        >
                            <Icon name="solar:logout-3-broken" size={18} />
                            Keluar
                        </Link>
                    </div>
                </Dropdown>
            </div>
        </header>
    );
}
