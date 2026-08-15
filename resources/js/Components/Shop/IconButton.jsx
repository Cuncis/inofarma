import Icon from './Icon';

/**
 * Tappable header action that runs a handler rather than navigating.
 *
 * The button twin of `IconLink`, used for the search toggle.
 *
 * @param {{ name: string, onClick: () => void, label: string }} props
 */
export default function IconButton({ name, onClick, label }) {
    return (
        <button
            type="button"
            onClick={onClick}
            aria-label={label}
            className="flex items-center"
        >
            <Icon name={name} size={20} />
        </button>
    );
}
