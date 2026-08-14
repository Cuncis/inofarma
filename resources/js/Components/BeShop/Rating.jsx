/**
 * Five-star rating rendered with text glyphs, matching the reference design.
 *
 * @param {{ score: number, size?: string }} props
 */
export default function Rating({ score, size = 'text-xs' }) {
    return (
        <div className={size}>
            {[1, 2, 3, 4, 5].map((star) => (
                <span key={star} className={star <= score ? 'text-star' : 'text-[#dddddd]'}>
                    &#9733;
                </span>
            ))}
        </div>
    );
}
