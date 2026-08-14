import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';

const sections = [
    {
        title: 'Shipping Policy',
        body: 'We offer free standard shipping on all orders over $50. Standard shipping takes 5-7 business days. Express shipping (2-3 business days) is available for $9.99.',
    },
    {
        title: 'Returns Policy',
        body: 'Items can be returned within 30 days of purchase in original condition with tags attached. Refunds are processed within 5-10 business days.',
    },
    {
        title: 'Payment Info',
        body: 'We accept Visa, MasterCard, AMEX, PayPal, Apple Pay, and Google Pay. All transactions are secured with SSL encryption.',
    },
];

export default function ShippingInfo() {
    return (
        <MobileLayout
            title="Shipping & Payment Info"
            header={<AppBar title="Shipping & Payment Info" back="/ui/profile" />}
        >
            <div className="flex-1 overflow-y-auto p-4">
                {sections.map((section) => (
                    <div key={section.title} className="mb-5">
                        <div className="mb-2.5 border-b-2 border-ink pb-2 font-display text-[15px]">
                            {section.title}
                        </div>

                        <p className="text-xs leading-[1.7] text-muted">{section.body}</p>
                    </div>
                ))}
            </div>
        </MobileLayout>
    );
}
