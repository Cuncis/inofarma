import { Head } from '@inertiajs/react';

/**
 * Mobile-locked app shell.
 *
 * The frame is capped at `max-w-app` (430px) and centred on every breakpoint, so
 * phones, tablets and desktops all render the exact same layout — desktops just
 * get a neutral gutter either side. The frame owns the viewport height; each
 * screen scrolls inside it rather than scrolling the page.
 *
 * @param {{
 *   title: string,
 *   children: import('react').ReactNode,
 *   header?: import('react').ReactNode,
 *   footer?: import('react').ReactNode,
 *   background?: string,
 * }} props
 */
export default function MobileLayout({
    title,
    children,
    header = null,
    footer = null,
    background = 'bg-canvas',
}) {
    return (
        <>
            <Head title={title} />

            <div className="flex h-screen [height:100dvh] justify-center bg-shell">
                <div
                    className={`relative flex h-full w-full max-w-app flex-col overflow-hidden font-shop text-ink ${background}`}
                >
                    {header}
                    {children}
                    {footer}
                </div>
            </div>
        </>
    );
}
