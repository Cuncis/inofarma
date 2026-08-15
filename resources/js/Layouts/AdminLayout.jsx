import { useState } from 'react';
import { Head } from '@inertiajs/react';
import Sidebar from '@/Components/Admin/Sidebar';
import Topbar from '@/Components/Admin/Topbar';
import PageTitle from '@/Components/Admin/PageTitle';

/**
 * Admin shell: fixed sidebar, sticky topbar, scrolling content area, footer.
 *
 * The sidebar has two independent states — `open` drives the slide-over on
 * small screens, `collapsed` switches to the icon rail from `lg` up — so the
 * hamburger means the right thing at every width.
 *
 * @param {{
 *   title: string,
 *   children: import('react').ReactNode,
 *   breadcrumb?: { label: string, href?: string }[],
 *   heading?: string,
 *   actions?: import('react').ReactNode,
 * }} props
 */
export default function AdminLayout({ title, children, breadcrumb, heading, actions }) {
    const [open, setOpen] = useState(false);
    const [collapsed, setCollapsed] = useState(false);

    return (
        <>
            <Head title={title} />

            <div className="min-h-screen bg-admin-bg text-admin-body dark:bg-admin-dark-bg dark:text-admin-dark-body">
                <Sidebar
                    open={open}
                    collapsed={collapsed}
                    onClose={() => setOpen(false)}
                />

                <div
                    className={`flex min-h-screen flex-col transition-[padding] duration-300 ${
                        collapsed ? 'lg:pl-sidebar-sm' : 'lg:pl-sidebar'
                    }`}
                >
                    <Topbar
                        onToggleSidebar={() => setOpen((current) => ! current)}
                        onToggleCollapse={() => setCollapsed((current) => ! current)}
                    />

                    <main className="flex-1 px-4 py-6 sm:px-6">
                        {heading || breadcrumb ? (
                            <PageTitle
                                heading={heading ?? title}
                                breadcrumb={breadcrumb}
                                actions={actions}
                            />
                        ) : null}

                        {children}
                    </main>

                    <footer className="flex h-[60px] shrink-0 items-center justify-between border-t border-admin-border px-4 text-xs text-admin-muted dark:border-admin-dark-border dark:text-admin-dark-muted sm:px-6">
                        <span>© {new Date().getFullYear()} Inofarma</span>
                        <span className="hidden sm:block">
                            Dibuat oleh tim Inofarma
                        </span>
                    </footer>
                </div>
            </div>
        </>
    );
}
