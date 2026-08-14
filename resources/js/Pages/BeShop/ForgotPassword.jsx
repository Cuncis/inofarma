import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Field from '@/Components/BeShop/Field';

export default function ForgotPassword() {
    return (
        <MobileLayout
            title="Forgot Password"
            header={<AppBar title="Forgot Password" back="/ui/signin" tone="white" />}
        >
            <div className="flex-1 overflow-y-auto p-5">
                <div className="bg-blush bg-[radial-gradient(circle_at_10%_90%,rgba(255,117,87,.2)_0%,transparent_60%)] p-6">
                    <p className="mb-5 text-[13px] leading-[1.7] text-muted">
                        Enter the email address associated with your account and we will
                        send you a link to reset your password.
                    </p>

                    <Field value="kristinwatson@mail.com" className="mb-2.5" />

                    <Button>Send</Button>
                </div>
            </div>
        </MobileLayout>
    );
}
