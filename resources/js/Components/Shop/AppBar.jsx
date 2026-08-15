import { Link } from '@inertiajs/react';
import Icon from './Icon';

/**
 * Fixed 48px screen header: optional back arrow or wordmark on the left, a
 * centred screen title, and up to two action icons on the right.
 *
 * @param {{
 *   title?: string,
 *   back?: string|boolean,
 *   brand?: boolean,
 *   actions?: import('react').ReactNode,
 *   tone?: 'blush'|'white'|'ink',
 * }} props
 */
export default function AppBar({ title, back, brand = false, actions, tone = 'blush' }) {
    const tones = {
        blush: 'bg-blush text-ink',
        white: 'bg-white text-ink',
        ink: 'bg-ink text-white',
    };

    return (
        <header
            className={`flex h-appbar shrink-0 items-center px-3.5 ${tones[tone]}`}
        >
            <div className="flex min-w-[40px] items-center">
                {back ? (
                    <Link href={typeof back === 'string' ? back : '#'} aria-label="Kembali">
                        <Icon name="back" size={20} />
                    </Link>
                ) : null}

                {brand ? (
                    <Link href="/" className="font-display text-base tracking-[2px]">
                        INOFARMA
                    </Link>
                ) : null}
            </div>

            <div className="flex-1 text-center">
                {title ? (
                    <span className="font-display text-sm uppercase tracking-[0.5px]">
                        {title}
                    </span>
                ) : null}
            </div>

            <div className="flex min-w-[40px] items-center justify-end gap-2">
                {actions}
            </div>
        </header>
    );
}
