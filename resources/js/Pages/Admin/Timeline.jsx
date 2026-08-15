import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import { activityTimeline } from '@/Components/Admin/data';

/** @type {Record<string, string>} */
const tones = {
    brand: 'bg-blush text-brand dark:bg-brand/20 dark:text-white',
    success: 'bg-[#e8f9e9] text-success-deep dark:bg-success/20 dark:text-success',
    warning: 'bg-[#fff1e3] text-warning-deep dark:bg-warning/20 dark:text-warning',
    danger: 'bg-[#fdecec] text-danger-deep dark:bg-danger/20 dark:text-danger',
    info: 'bg-[#e6f7f5] text-info-deep dark:bg-info/20 dark:text-info',
};

export default function Timeline() {
    return (
        <AdminLayout
            title="Linimasa"
            heading="Linimasa Aktivitas"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Linimasa' }]}
        >
            <div className="mx-auto max-w-3xl space-y-6">
                {activityTimeline.map((group) => (
                    <div key={group.date}>
                        <p className="mb-3 inline-flex rounded-md bg-admin-hover px-3 py-1.5 text-xs font-bold text-admin-heading dark:bg-admin-dark-hover dark:text-admin-dark-heading">
                            {group.date}
                        </p>

                        <ol className="relative space-y-4 border-l border-admin-border pl-8 dark:border-admin-dark-border">
                            {group.items.map((item) => (
                                <li key={item.title} className="relative">
                                    <span
                                        className={`absolute -left-[46px] flex h-9 w-9 items-center justify-center rounded-full ${tones[item.tone]}`}
                                    >
                                        <Icon name={item.icon} size={18} />
                                    </span>

                                    <Card bodyClassName="p-4">
                                        <div className="flex flex-wrap items-baseline justify-between gap-2">
                                            <h3 className="text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                                {item.title}
                                            </h3>
                                            <span className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                                {item.time}
                                            </span>
                                        </div>

                                        <p className="mt-1 text-[13px] leading-relaxed text-admin-body dark:text-admin-dark-body">
                                            {item.body}
                                        </p>
                                    </Card>
                                </li>
                            ))}
                        </ol>
                    </div>
                ))}
            </div>
        </AdminLayout>
    );
}
