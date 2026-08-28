import { useState } from 'react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import { faqs } from '@/Components/Shop/data';

export default function Faq() {
    const [open, setOpen] = useState(faqs[0].question);

    return (
        <MobileLayout title="FAQ" header={<AppBar title="FAQ" back="/ui/profile" tone="brand" />}>
            <div className="flex-1 overflow-y-auto p-3.5">
                {faqs.map((faq) => {
                    const expanded = open === faq.question;

                    return (
                        <div key={faq.question} className="mb-2 border border-line bg-white">
                            <button
                                type="button"
                                onClick={() => setOpen(expanded ? null : faq.question)}
                                aria-expanded={expanded}
                                className="flex w-full justify-between p-3.5 text-left text-[13px] font-bold"
                            >
                                <span>{faq.question}</span>
                                <span
                                    className={`text-brand transition-transform duration-300 ease-in-out ${
                                        expanded ? 'rotate-180' : ''
                                    }`}
                                >
                                    {expanded ? '−' : '+'}
                                </span>
                            </button>

                            {/*
                             * `grid-rows-[0fr]` → `[1fr]` animates height without
                             * measuring it in JS — the content is always mounted
                             * (never conditionally removed), so the transition has
                             * something to animate between.
                             */}
                            <div
                                className={`grid transition-[grid-template-rows] duration-300 ease-in-out ${
                                    expanded ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'
                                }`}
                            >
                                <div className="overflow-hidden">
                                    <div className="px-3.5 pb-3.5 text-xs leading-relaxed text-muted">
                                        {faq.answer ??
                                            'Tim kami siap membantu — hubungi kami lewat aplikasi dan kami akan membalas dalam satu hari kerja.'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    );
                })}
            </div>
        </MobileLayout>
    );
}
