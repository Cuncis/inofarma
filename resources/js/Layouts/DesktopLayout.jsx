import { Head } from '@inertiajs/react';

/**
 * Desktop page shell: a normal scrolling document (unlike `MobileLayout`'s
 * fixed-height phone frame) with a full-bleed header and a centred content
 * column. There is deliberately no bottom tab bar here.
 *
 * @param {{
 *   title: string,
 *   children: import('react').ReactNode,
 *   header?: import('react').ReactNode,
 * }} props
 */
export default function DesktopLayout({ title, children, header = null }) {
    return (
        <>
            <Head title={title} />

            <div className="min-h-screen bg-canvas font-shop text-ink">
                {header}
                <main className="mx-auto w-full max-w-6xl px-6 pb-12">{children}</main>
            </div>
        </>
    );
}
