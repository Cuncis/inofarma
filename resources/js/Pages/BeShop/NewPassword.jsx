import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Field from '@/Components/BeShop/Field';

export default function NewPassword() {
    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/signin');
    };

    return (
        <MobileLayout
            title="New Password"
            header={<AppBar title="Reset Password" back="/ui/email-sent" tone="white" />}
        >
            <form onSubmit={submit} className="flex-1 overflow-y-auto p-[18px]">
                <p className="mb-4 text-[13px] leading-relaxed text-muted">
                    Enter new password and confirm.
                </p>

                <Field
                    type="password"
                    name="password"
                    placeholder="New password"
                    className="mb-2.5"
                />
                <Field
                    type="password"
                    name="password_confirmation"
                    placeholder="Confirm new password"
                    className="mb-2.5"
                />

                <Button type="submit">Change Password</Button>
            </form>
        </MobileLayout>
    );
}
