import { Head, Link } from '@inertiajs/react';
import Icon from '@/Components/Admin/Icon';

/**
 * Standalone shell for the admin screens that sit outside the app chrome —
 * sign in, sign up, password reset, lock screen and the error pages.
 *
 * @param {{
 *   title: string,
 *   children: import('react').ReactNode,
 *   heading?: string,
 *   subheading?: string,
 *   width?: string,
 * }} props
 */
export default function AdminAuthLayout({
    title,
    children,
    heading,
    subheading,
    width = 'max-w-md',
}) {
    return (
        <>
            <Head title={title} />

            <div className="flex min-h-screen flex-col items-center justify-center bg-admin-bg px-4 py-10 dark:bg-admin-dark-bg">
                <Link href="/admin" className="mb-6 flex items-center gap-2.5">
                    <span className="flex h-10 w-10 items-center justify-center rounded-lg bg-brand text-white">
                        <Icon name="solar:pill-bold-duotone" size={24} />
                    </span>
                    <span className="text-xl font-bold tracking-tight text-admin-heading dark:text-admin-dark-heading">
                        Inofarma
                    </span>
                </Link>

                <div
                    className={`w-full ${width} rounded-xl border border-admin-border bg-admin-card p-7 shadow-card dark:border-admin-dark-border dark:bg-admin-dark-card`}
                >
                    {heading ? (
                        <div className="mb-6 text-center">
                            <h1 className="text-lg font-semibold text-admin-heading dark:text-admin-dark-heading">
                                {heading}
                            </h1>

                            {subheading ? (
                                <p className="mt-1.5 text-[13px] text-admin-muted dark:text-admin-dark-muted">
                                    {subheading}
                                </p>
                            ) : null}
                        </div>
                    ) : null}

                    {children}
                </div>
            </div>
        </>
    );
}
