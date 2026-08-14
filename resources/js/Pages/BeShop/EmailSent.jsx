import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import { asset } from '@/Components/BeShop/data';

export default function EmailSent() {
    return (
        <MobileLayout
            title="Password Email Sent"
            header={
                <AppBar title="Reset Password" back="/ui/forgot-password" tone="white" />
            }
        >
            <div className="flex flex-1 flex-col items-center justify-center overflow-y-auto bg-blush p-6 text-center">
                <img
                    src={asset.other('04')}
                    alt=""
                    className="mx-auto mb-4 h-[180px] w-[180px] object-contain"
                />

                <h2 className="mb-2.5 font-display text-[22px]">Email Sent!</h2>

                <p className="mb-[22px] text-[13px] leading-relaxed text-muted">
                    We have sent a password recover
                    <br />
                    instructions to your email.
                </p>

                <Button href="/ui/new-password">OK, Got It!</Button>

                <div className="mt-2.5 flex gap-1 text-xs">
                    <span>Did not receive the email?</span>
                    <Link href="/ui/forgot-password" className="text-brand">
                        Resend
                    </Link>
                </div>
            </div>
        </MobileLayout>
    );
}
