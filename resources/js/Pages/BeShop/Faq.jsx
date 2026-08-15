import { useState } from 'react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import { faqs } from '@/Components/BeShop/data';

export default function Faq() {
    const [open, setOpen] = useState(faqs[0].question);

    return (
        <MobileLayout title="FAQ" header={<AppBar title="FAQ" back="/ui/profile" />}>
            <div className="flex-1 overflow-y-auto p-3.5">
                {faqs.map((faq) => {
                    const expanded = open === faq.question;

                    return (
                        <div key={faq.question} className="mb-2 border border-line">
                            <button
                                type="button"
                                onClick={() => setOpen(expanded ? null : faq.question)}
                                aria-expanded={expanded}
                                className="flex w-full justify-between p-3.5 text-left text-[13px] font-bold"
                            >
                                <span>{faq.question}</span>
                                <span className="text-brand">{expanded ? '−' : '+'}</span>
                            </button>

                            {expanded ? (
                                <div className="px-3.5 pb-3.5 text-xs leading-relaxed text-muted">
                                    {faq.answer ??
                                        'Tim kami siap membantu — hubungi kami lewat aplikasi dan kami akan membalas dalam satu hari kerja.'}
                                </div>
                            ) : null}
                        </div>
                    );
                })}
            </div>
        </MobileLayout>
    );
}
