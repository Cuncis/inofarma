import { Link } from '@inertiajs/react';
import Icon from './Icon';

const tabs = [
    { key: 'home', label: 'Home', icon: 'home', route: '/ui/home' },
    { key: 'order', label: 'Order', icon: 'bagSimple', route: '/ui/cart' },
    { key: 'wishlist', label: 'Wishlist', icon: 'heart', route: '/ui/wishlist' },
    { key: 'profile', label: 'Profile', icon: 'user', route: '/ui/profile' },
];

/**
 * Bottom navigation shared by the four main tabs and the empty states that sit
 * inside them.
 *
 * @param {{ active?: 'home'|'order'|'wishlist'|'profile' }} props
 */
export default function TabBar({ active }) {
    return (
        <nav className="flex h-tabbar shrink-0 items-center border-t border-[#f0f0f0] bg-white shadow-[0_-2px_8px_rgba(0,0,0,.04)]">
            {tabs.map((tab) => {
                const isActive = tab.key === active;

                return (
                    <Link
                        key={tab.key}
                        href={tab.route}
                        className={`flex flex-1 flex-col items-center gap-0.5 ${
                            isActive ? 'text-brand' : 'text-[#cccccc]'
                        }`}
                    >
                        <Icon name={tab.icon} size={21} />
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
