import { Link } from '@inertiajs/react';

const variants = {
    primary: 'bg-ink text-white',
    outline: 'border-2 border-ink bg-transparent text-ink',
};

/**
 * Full-width 52px action button in the two variants used across the screens.
 *
 * @param {{
 *   children: import('react').ReactNode,
 *   variant?: 'primary'|'outline',
 *   href?: string,
 *   className?: string,
 * }} props
 */
export default function Button({ children, variant = 'primary', href, className = '' }) {
    const classes = `flex h-control w-full items-center justify-center text-xs font-bold uppercase tracking-[1.5px] ${variants[variant]} ${className}`;

    if (href) {
        return (
            <Link href={href} className={classes}>
                {children}
            </Link>
        );
    }

    return (
        <button type="button" className={classes}>
            {children}
        </button>
    );
}
