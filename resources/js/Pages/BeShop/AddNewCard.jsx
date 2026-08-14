import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Field from '@/Components/BeShop/Field';

export default function AddNewCard() {
    return (
        <MobileLayout
            title="Add a New Card"
            header={
                <AppBar title="Add a New Card" back="/ui/payment-methods" tone="white" />
            }
        >
            <div className="flex-1 overflow-y-auto p-4">
                <div className="mb-5 rounded bg-gradient-to-br from-[#252525] to-[#555555] p-[22px] text-white">
                    <div className="mb-4 text-[11px] opacity-60">Credit Card</div>

                    <div className="mb-4 font-mono text-[15px] tracking-[2px]">
                        1234 5678 9012 3456
                    </div>

                    <div className="flex justify-between text-xs opacity-80">
                        <span>KRISTIN WATSON</span>
                        <span>12/26</span>
                    </div>
                </div>

                <Field value="Card number" className="mb-2.5" />
                <Field value="Cardholder name" className="mb-2.5" />

                <div className="mb-2.5 grid grid-cols-2 gap-2.5">
                    <Field value="MM/YY" placeholder />
                    <Field value="CVV" placeholder />
                </div>

                <Button>Add Card</Button>
            </div>
        </MobileLayout>
    );
}
