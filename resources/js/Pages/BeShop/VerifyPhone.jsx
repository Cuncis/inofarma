import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Field from '@/Components/BeShop/Field';

export default function VerifyPhone() {
    return (
        <MobileLayout
            title="Verify Phone Number"
            header={<AppBar title="Verify Phone Number" back="/ui/signup" tone="white" />}
        >
            <div className="flex-1 overflow-y-auto p-5">
                <div className="bg-blush p-6">
                    <p className="mb-[18px] text-[13px] leading-[1.7] text-muted">
                        We have sent you an SMS with a code to number{' '}
                        <strong>+17 0123456789</strong>.
                    </p>

                    <Field
                        value="+17123456789"
                        icon="check"
                        iconClassName="text-success"
                        className="mb-2.5"
                    />

                    <Button>Confirm</Button>
                </div>
            </div>
        </MobileLayout>
    );
}
