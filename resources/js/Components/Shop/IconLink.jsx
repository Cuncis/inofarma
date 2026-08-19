import { Link } from '@inertiajs/react';
import Icon from './Icon';

/**
 * Tappable header action, with an optional small count badge pinned to its
 * top-right corner (the cart icon's item count). Red rather than the brand
 * blue on purpose — this sits on headers that are themselves blue now
 * (`AppBar` tone="brand"), so a blue badge would disappear into it.
 *
 * @param {{ name: string, href: string, label: string, size?: number, badge?: number }} props
 */
export default function IconLink({ name, href, label, size = 19, badge = 0 }) {
    return (
        <Link href={href} aria-label={label} className="relative flex items-center">
            <Icon name={name} size={size} />

            {badge > 0 ? (
                <span className="absolute -right-1.5 -top-1.5 flex h-3.5 min-w-[14px] items-center justify-center rounded-full bg-danger px-[3px] text-[8px] font-bold text-white">
                    {badge}
                </span>
            ) : null}
        </Link>
    );
}
