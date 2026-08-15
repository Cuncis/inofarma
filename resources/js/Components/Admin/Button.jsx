import { Link } from '@inertiajs/react';
import Icon from './Icon';

/** @type {Record<string, string>} */
const variants = {
    primary: 'bg-brand text-white hover:bg-brand/90',
    success: 'bg-success text-ink hover:bg-success/90',
    warning: 'bg-warning text-ink hover:bg-warning/90',
    danger: 'bg-danger text-white hover:bg-danger/90',
    outline:
        'border border-admin-border text-admin-body hover:bg-admin-hover dark:border-admin-dark-border dark:text-admin-dark-body dark:hover:bg-admin-dark-hover',
    soft: 'bg-blush text-brand hover:bg-blush/70 dark:bg-brand/20 dark:text-white',
};

/** @type {Record<string, string>} */
const sizes = {
    sm: 'h-8 px-3 text-xs',
    md: 'h-10 px-4 text-[13px]',
};

/**
 * Renders an Inertia link when `href` is given, otherwise a plain button.
 *
 * @param {{
 *   children?: import('react').ReactNode,
 *   href?: string,
 *   type?: string,
 *   variant?: keyof typeof variants,
 *   size?: keyof typeof sizes,
 *   icon?: string,
 *   onClick?: () => void,
 *   disabled?: boolean,
 *   className?: string,
 * }} props
 */
export default function Button({
    children,
    href,
    type = 'button',
    variant = 'primary',
    size = 'md',
    icon,
    onClick,
    disabled = false,
    className = '',
    ...rest
}) {
    const classes = `inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition-colors disabled:cursor-not-allowed disabled:opacity-60 ${variants[variant]} ${sizes[size]} ${className}`;

    const inner = (
        <>
            {icon ? <Icon name={icon} size={size === 'sm' ? 15 : 17} /> : null}
            {children}
        </>
    );

    if (href) {
        return (
            <Link href={href} className={classes} {...rest}>
                {inner}
            </Link>
        );
    }

    return (
        <button
            type={type}
            onClick={onClick}
            disabled={disabled}
            className={classes}
            {...rest}
        >
            {inner}
        </button>
    );
}
