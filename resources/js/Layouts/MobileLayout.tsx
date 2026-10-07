import { Head } from '@inertiajs/react';
import { isValidElement } from 'react';
import DesktopHeader from '@/Components/Shop/DesktopHeader';
import TabBar from '@/Components/Shop/TabBar';
import useIsDesktop from '@/Components/Shop/useIsDesktop';
import DesktopLayout from './DesktopLayout';

/**
 * Storefront screen shell.
 *
 * Below the `lg` breakpoint it is the phone frame: capped at `max-w-app`
 * (430px) and centred, owning the viewport height, with each screen scrolling
 * inside it rather than the page scrolling.
 *
 * From `lg` up the same screen content is shown as a centred card under the
 * desktop header instead (see `DesktopLayout`). The bottom `TabBar` is
 * dropped there; any other footer (an action bar) stays at the card's bottom.
 */
export default function MobileLayout({
    title,
    children,
    header = null,
    footer = null,
    background = 'bg-canvas',
}: {
  title: string,
  children: import('react').ReactNode,
  header?: import('react').ReactNode,
  footer?: import('react').ReactNode,
  background?: string,
}) {
    const isDesktop = useIsDesktop();

    if (isDesktop) {
        return (
            <DesktopLayout title={title} header={<DesktopHeader />} narrow>
                <div
                    className={`relative flex min-h-[60vh] flex-col overflow-hidden border border-line ${background}`}
                >
                    {header}
                    {children}
                    {isValidElement(footer) && footer.type === TabBar ? null : footer}
                </div>
            </DesktopLayout>
        );
    }

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
