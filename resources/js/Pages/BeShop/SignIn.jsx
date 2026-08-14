import MobileLayout from '@/Layouts/MobileLayout';
import Button from '@/Components/BeShop/Button';
import Field from '@/Components/BeShop/Field';
import SocialButtons from '@/Components/BeShop/SocialButtons';

export default function SignIn() {
    return (
        <MobileLayout title="Sign In" background="bg-blush">
            <div className="flex flex-1 flex-col items-center justify-center overflow-y-auto px-[22px] py-6">
                <div className="mb-6 font-display text-[22px] tracking-[2px]">BESHOP</div>

                <h1 className="mb-2 font-display text-2xl">Sign in</h1>

                <p className="mb-5 text-center text-[13px] text-muted">
                    Use social networks or your email
                </p>

                <SocialButtons />

                <div className="w-full">
                    <Field value="kristinwatson@mail.com" className="mb-2.5" />
                    <Field value="••••••••" icon="eyeOff" className="mb-2.5" />
                </div>

                <div className="mb-3.5 flex w-full justify-between text-xs">
                    <div className="flex items-center gap-1.5">
                        <div className="h-[15px] w-[15px] border border-[#dddddd] bg-white" />
                        Remember me
                    </div>

                    <span className="text-brand">Lost your password?</span>
                </div>

                <Button>Sign In</Button>

                <div className="mt-3 flex gap-1 text-xs">
                    <span>No account?</span>
                    <span className="text-brand">Register now</span>
                </div>
            </div>
        </MobileLayout>
    );
}
