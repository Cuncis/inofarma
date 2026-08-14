import { Link } from '@inertiajs/react';

const variants = {
    primary: 'bg-ink text-white',
    outline: 'border-2 border-ink bg-transparent text-ink',
};

/**
 * Full-width 52px action.
 *
 * Renders an Inertia `<Link>` when `href` is given (adding `method` turns it into
 * a non-GET visit), and a plain `<button>` otherwise.
 *
 * @param {{
 *   children: import('react').ReactNode,
 *   variant?: 'primary'|'outline',
 *   href?: string,
 *   method?: 'get'|'post'|'put'|'patch'|'delete',
 *   onClick?: () => void,
 *   type?: 'button'|'submit',
 *   disabled?: boolean,
 *   className?: string,
 * }} props
 */
export default function Button({
    children,
    variant = 'primary',
    href,
    method,
    onClick,
    type = 'button',
    disabled = false,
    className = '',
}) {
    const classes = `flex h-control w-full items-center justify-center text-xs font-bold uppercase tracking-[1.5px] transition-opacity active:opacity-80 ${
        variants[variant]
    } ${disabled ? 'opacity-60' : ''} ${className}`;

    if (href) {
        return (
            <Link
                href={href}
                method={method}
                as={method ? 'button' : 'a'}
                className={classes}
            >
                {children}
            </Link>
        );
    }

    return (
        <button type={type} onClick={onClick} disabled={disabled} className={classes}>
            {children}
        </button>
    );
}
