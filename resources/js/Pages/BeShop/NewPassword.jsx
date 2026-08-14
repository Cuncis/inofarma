import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Field from '@/Components/BeShop/Field';

export default function NewPassword() {
    return (
        <MobileLayout
            title="New Password"
            header={<AppBar title="Reset Password" back="/ui/email-sent" tone="white" />}
        >
            <div className="flex-1 overflow-y-auto p-[18px]">
                <p className="mb-4 text-[13px] leading-relaxed text-muted">
                    Enter new password and confirm.
                </p>

                <Field value="New password" icon="eyeOff" className="mb-2.5" />
                <Field value="Confirm new password" icon="eyeOff" className="mb-2.5" />

                <Button>Change Password</Button>
            </div>
        </MobileLayout>
    );
}
