import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';

const digits = ['4', '2', '7', '1', ''];

export default function OtpCode() {
    return (
        <MobileLayout
            title="Confirmation Code"
            header={
                <AppBar title="Verify Phone Number" back="/ui/verify-phone" tone="white" />
            }
        >
            <div className="flex-1 overflow-y-auto p-5">
                <div className="bg-blush p-6">
                    <p className="mb-[18px] text-[13px] leading-[1.7] text-muted">
                        Enter your OTP code here.
                    </p>

                    <div className="mb-4 flex gap-2">
                        {digits.map((digit, index) => (
                            <div
                                key={index}
                                className={`flex aspect-square flex-1 items-center justify-center text-xl font-bold ${
                                    digit
                                        ? 'border-2 border-ink'
                                        : 'border border-[#dddddd]'
                                }`}
                            >
                                {digit}
                            </div>
                        ))}
                    </div>

                    <div className="mb-4 flex gap-1 text-xs">
                        <span>Did not receive the OTP?</span>
                        <span className="text-brand">Resend.</span>
                    </div>

                    <Button>Verify</Button>
                </div>
            </div>
        </MobileLayout>
    );
}
