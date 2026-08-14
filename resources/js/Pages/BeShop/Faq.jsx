import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import { faqs } from '@/Components/BeShop/data';

export default function Faq() {
    return (
        <MobileLayout title="FAQ" header={<AppBar title="FAQ" back="/ui/profile" />}>
            <div className="flex-1 overflow-y-auto p-3.5">
                {faqs.map((faq) => (
                    <div key={faq.question} className="mb-2 border border-line">
                        <div className="flex justify-between p-3.5 text-[13px] font-bold">
                            <span>{faq.question}</span>
                            <span className="text-brand">{faq.open ? '−' : '+'}</span>
                        </div>

                        {faq.open ? (
                            <div className="px-3.5 pb-3.5 text-xs leading-relaxed text-muted">
                                {faq.answer}
                            </div>
                        ) : null}
                    </div>
                ))}
            </div>
        </MobileLayout>
    );
}
