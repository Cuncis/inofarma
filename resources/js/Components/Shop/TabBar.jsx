import { Link, usePage } from '@inertiajs/react';
import Icon from './Icon';

const tabs = [
    { key: 'home', label: 'Beranda', icon: 'home', route: '/' },
    { key: 'order', label: 'Pesanan', icon: 'bagSimple', route: '/ui/cart' },
    { key: 'wishlist', label: 'Favorit', icon: 'heart', route: '/ui/wishlist' },
    { key: 'profile', label: 'Profil', icon: 'user', route: '/ui/profile' },
];

/**
 * Bottom navigation shared by the four main tabs and the empty states that sit
 * inside them.
 *
 * @param {{ active?: 'home'|'order'|'wishlist'|'profile' }} props
 */
export default function TabBar({ active }) {
    const { cartCount } = usePage().props;

    return (
        <nav className="flex h-tabbar shrink-0 items-center border-t border-[#f0f0f0] bg-white shadow-[0_-2px_8px_rgba(0,0,0,.04)]">
            {tabs.map((tab) => {
                const isActive = tab.key === active;

                return (
                    <Link
                        key={tab.key}
                        href={tab.route}
                        className={`relative flex flex-1 flex-col items-center gap-0.5 ${
                            isActive ? 'text-brand' : 'text-[#cccccc]'
                        }`}
                    >
                        <span className="relative">
                            <Icon name={tab.icon} size={21} />

                            {tab.key === 'order' && cartCount > 0 ? (
                                <span className="absolute -right-2 -top-1.5 flex h-3.5 min-w-[14px] items-center justify-center rounded-full bg-brand px-[3px] text-[8px] font-bold text-white">
                                    {cartCount}
                                </span>
                            ) : null}
                        </span>
                        <span
                            className={`text-[9px] ${
                                isActive ? 'text-brand' : 'text-[#aaaaaa]'
                            }`}
                        >
                            {tab.label}
                        </span>
                    </Link>
                );
            })}
        </nav>
    );
}
