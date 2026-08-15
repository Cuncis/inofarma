import { Link } from '@inertiajs/react';
import Icon from './Icon';

/**
 * Tappable header action.
 *
 * @param {{ name: string, href: string, label: string, size?: number }} props
 */
export default function IconLink({ name, href, label, size = 19 }) {
    return (
        <Link href={href} aria-label={label} className="flex items-center">
            <Icon name={name} size={size} />
        </Link>
    );
}
