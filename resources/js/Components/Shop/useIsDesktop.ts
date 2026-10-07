import { useEffect, useState } from 'react';

const DESKTOP_QUERY = '(min-width: 1024px)';

/**
 * True at Tailwind's `lg` breakpoint and up. Home uses it to mount either the
 * phone shell or the desktop page, rather than rendering both and hiding one
 * (which would run two carousels and double every request for banner art).
 */
export default function useIsDesktop(): boolean {
    const [isDesktop, setIsDesktop] = useState(
        () => typeof window !== 'undefined' && window.matchMedia(DESKTOP_QUERY).matches,
    );

    useEffect(() => {
        const query = window.matchMedia(DESKTOP_QUERY);
        const onChange = (event: MediaQueryListEvent) => setIsDesktop(event.matches);

        query.addEventListener('change', onChange);

        return () => query.removeEventListener('change', onChange);
    }, []);

    return isDesktop;
}
