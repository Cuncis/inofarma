import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import { helpTopics } from '@/Components/Admin/data';

export default function HelpCenter() {
    const [search, setSearch] = useState('');

    const visible = helpTopics.filter(
        (topic) =>
            topic.title.toLowerCase().includes(search.toLowerCase()) ||
            topic.description.toLowerCase().includes(search.toLowerCase()),
    );

    return (
        <AdminLayout
            title="Pusat Bantuan"
            heading="Pusat Bantuan"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Pusat Bantuan' }]}
        >
            <div className="mx-auto mb-6 max-w-xl text-center">
                <h2 className="text-lg font-semibold text-admin-heading dark:text-admin-dark-heading">
                    Ada yang bisa kami bantu?
                </h2>
                <p className="mt-1.5 text-[13px] text-admin-muted dark:text-admin-dark-muted">
                    Cari panduan berdasarkan topik atau kata kunci.
                </p>

                <div className="relative mt-4">
                    <Icon
                        name="solar:magnifer-linear"
                        size={18}
                        className="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-admin-muted"
                    />
                    <input
                        type="search"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Cari panduan..."
                        className="h-11 w-full rounded-lg border border-admin-border bg-admin-card pl-11 pr-4 text-[13px] text-admin-body placeholder:text-admin-muted focus:border-brand focus:outline-none focus:ring-0 dark:border-admin-dark-border dark:bg-admin-dark-card dark:text-admin-dark-body"
                    />
                </div>
            </div>

            {visible.length === 0 ? (
                <Card>
                    <p className="py-6 text-center text-[13px] text-admin-muted dark:text-admin-dark-muted">
                        Tidak ada topik yang cocok.
                    </p>
                </Card>
            ) : (
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {visible.map((topic) => (
                        <Card key={topic.title}>
                            <span className="mb-3 flex h-11 w-11 items-center justify-center rounded-lg bg-blush text-brand dark:bg-brand/20 dark:text-white">
                                <Icon name={topic.icon} size={24} />
                            </span>

                            <h3 className="text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                {topic.title}
                            </h3>

                            <p className="mt-1 text-[13px] leading-relaxed text-admin-body dark:text-admin-dark-body">
                                {topic.description}
                            </p>

                            <p className="mt-3 text-xs font-medium text-brand">
                                {topic.count} artikel
                            </p>
                        </Card>
                    ))}
                </div>
            )}
        </AdminLayout>
    );
}
