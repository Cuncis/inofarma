import Icon from './Icon';

/**
 * Square checkbox matching the reference design.
 *
 * @param {{ checked: boolean, onChange: () => void, label?: import('react').ReactNode, size?: number }} props
 */
export default function Checkbox({ checked, onChange, label, size = 15 }) {
    return (
        <button
            type="button"
            onClick={onChange}
            aria-pressed={checked}
            className="flex items-center gap-2.5"
        >
            <span
                style={{ width: size, height: size }}
                className={`flex items-center justify-center border bg-white ${
                    checked ? 'border-brand text-brand' : 'border-[#cccccc]'
                }`}
            >
                {checked ? <Icon name="check" size={size - 4} /> : null}
            </span>

            {label}
        </button>
    );
}
