import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import { adminFaqs } from '@/Components/Admin/data';

export default function Faq() {
    const [open, setOpen] = useState(adminFaqs[0].question);

    return (
        <AdminLayout
            title="FAQ"
            heading="Pertanyaan Umum"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'FAQ' }]}
        >
            <div className="mx-auto max-w-3xl space-y-3">
                {adminFaqs.map((faq) => {
                    const expanded = open === faq.question;

                    return (
                        <Card key={faq.question} bodyClassName="p-0">
                            <button
                                type="button"
                                onClick={() => setOpen(expanded ? null : faq.question)}
                                aria-expanded={expanded}
                                className="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
                            >
                                <span className="text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {faq.question}
                                </span>

                                <Icon
                                    name="solar:alt-arrow-down-bold-duotone"
                                    size={16}
                                    className={`shrink-0 text-admin-muted transition-transform ${
                                        expanded ? 'rotate-180' : ''
                                    }`}
                                />
                            </button>

                            {expanded ? (
                                <p className="border-t border-admin-border px-5 py-4 text-[13px] leading-relaxed text-admin-body dark:border-admin-dark-border dark:text-admin-dark-body">
                                    {faq.answer}
                                </p>
                            ) : null}
                        </Card>
                    );
                })}
            </div>
        </AdminLayout>
    );
}
