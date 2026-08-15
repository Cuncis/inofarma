import { useState } from 'react';
import Icon from './Icon';

/**
 * Flat 52px input row.
 *
 * Renders a real `<input>` so the prototype screens can be typed into. Password
 * fields get a working reveal toggle; any other trailing glyph is decorative
 * unless `onIconClick` is supplied.
 *
 * @param {{
 *   type?: string,
 *   name?: string,
 *   value?: string,
 *   defaultValue?: string,
 *   placeholder?: string,
 *   onChange?: (event: import('react').ChangeEvent<HTMLInputElement>) => void,
 *   icon?: string,
 *   iconClassName?: string,
 *   onIconClick?: () => void,
 *   error?: string,
 *   className?: string,
 * }} props
 */
export default function Field({
    type = 'text',
    name,
    value,
    defaultValue,
    placeholder,
    onChange,
    icon,
    iconClassName = 'text-[#aaaaaa]',
    onIconClick,
    error,
    className = '',
    ...rest
}) {
    const [revealed, setRevealed] = useState(false);

    const isPassword = type === 'password';

    return (
        <div className={className}>
            <div
                className={`flex h-control items-center gap-2 border bg-white px-3.5 ${
                    error ? 'border-brand' : 'border-blush'
                }`}
            >
                <input
                    type={isPassword && revealed ? 'text' : type}
                    name={name}
                    value={value}
                    defaultValue={defaultValue}
                    placeholder={placeholder}
                    onChange={onChange}
                    className="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-[13px] text-muted placeholder:text-[#bbbbbb] focus:border-0 focus:outline-none focus:ring-0"
                    {...rest}
                />

                {isPassword ? (
                    <button
                        type="button"
                        onClick={() => setRevealed((shown) => ! shown)}
                        aria-label={revealed ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'}
                    >
                        <Icon
                            name={revealed ? 'eye' : 'eyeOff'}
                            size={17}
                            className={iconClassName}
                        />
                    </button>
                ) : icon ? (
                    onIconClick ? (
                        <button type="button" onClick={onIconClick}>
                            <Icon name={icon} size={17} className={iconClassName} />
                        </button>
                    ) : (
                        <Icon name={icon} size={17} className={iconClassName} />
                    )
                ) : null}
            </div>

            {error ? <p className="mt-1 text-[11px] text-brand">{error}</p> : null}
        </div>
    );
}
