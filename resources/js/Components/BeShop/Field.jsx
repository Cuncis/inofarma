import Icon from './Icon';

/**
 * Flat 52px input row. The screens are layout-only, so this renders a static
 * value rather than a controlled `<input>`.
 *
 * @param {{
 *   value: string,
 *   icon?: string,
 *   iconClassName?: string,
 *   placeholder?: boolean,
 *   className?: string,
 * }} props
 */
export default function Field({
    value,
    icon,
    iconClassName = 'text-[#aaaaaa]',
    placeholder = false,
    className = '',
}) {
    return (
        <div
            className={`flex h-control items-center gap-2 border border-blush bg-white px-3.5 text-[13px] ${
                placeholder ? 'text-[#bbbbbb]' : 'text-muted'
            } ${className}`}
        >
            <span className="flex-1 truncate">{value}</span>

            {icon ? <Icon name={icon} size={17} className={iconClassName} /> : null}
        </div>
    );
}
