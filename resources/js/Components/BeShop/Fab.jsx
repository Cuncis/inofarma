import { Link } from '@inertiajs/react';
import Icon from './Icon';

/**
 * Floating "add" action pinned to the bottom-right of the screen frame.
 *
 * @param {{ href?: string, label: string }} props
 */
export default function Fab({ href = '#', label }) {
    return (
        <Link
            href={href}
            aria-label={label}
            className="absolute bottom-[18px] right-[18px] z-20 flex h-[52px] w-[52px] items-center justify-center rounded-full bg-brand text-white shadow-[0_4px_14px_rgba(208,82,120,.4)]"
        >
            <Icon name="plus" size={24} />
        </Link>
    );
}
