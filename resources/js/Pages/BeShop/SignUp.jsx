import { Link, router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Field from '@/Components/BeShop/Field';
import SocialButtons from '@/Components/BeShop/SocialButtons';

export default function SignUp() {
    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/verify-phone');
    };

    return (
        <MobileLayout
            title="Sign Up"
            header={<AppBar title="Sign Up" back="/ui/signin" tone="white" />}
        >
            <form
                onSubmit={submit}
                className="flex-1 overflow-y-auto bg-blush px-[22px] py-[18px]"
            >
                <h1 className="mb-2 text-center font-display text-[22px]">Sign up</h1>

                <p className="mb-[18px] text-center text-[13px] text-muted">
                    Use social networks or your email
                </p>

                <SocialButtons />

                <Field name="name" placeholder="Kristin Watson" className="mb-2.5" />
                <Field
                    type="email"
                    name="email"
                    placeholder="kristinwatson@mail.com"
                    className="mb-2.5"
                />
                <Field
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    className="mb-2.5"
                />
                <Field
                    type="password"
                    name="password_confirmation"
                    placeholder="Confirm your password"
                    className="mb-2.5"
                />

                <Button type="submit">Sign Up</Button>

                <div className="mt-2.5 flex justify-center gap-1 text-xs">
                    <span>Already have an account?</span>
                    <Link href="/ui/signin" className="text-brand">
                        Sign in.
                    </Link>
                </div>
            </form>
        </MobileLayout>
    );
}
