import { useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import Button from '@/Components/BeShop/Button';
import Checkbox from '@/Components/BeShop/Checkbox';
import Field from '@/Components/BeShop/Field';
import SocialButtons from '@/Components/BeShop/SocialButtons';

export default function SignIn() {
    const [remember, setRemember] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    });

    const submit = (event) => {
        event.preventDefault();

        post('/ui/signin');
    };

    return (
        <MobileLayout title="Sign In" background="bg-blush">
            <form
                onSubmit={submit}
                className="flex flex-1 flex-col items-center justify-center overflow-y-auto px-[22px] py-6"
            >
                <div className="mb-6 font-display text-[22px] tracking-[2px]">BESHOP</div>

                <h1 className="mb-2 font-display text-2xl">Sign in</h1>

                <p className="mb-5 text-center text-[13px] text-muted">
                    Use social networks or your email
                </p>

                <SocialButtons />

                <div className="w-full">
                    <Field
                        type="email"
                        name="email"
                        value={data.email}
                        onChange={(event) => setData('email', event.target.value)}
                        placeholder="kristinwatson@mail.com"
                        error={errors.email}
                        autoComplete="email"
                        className="mb-2.5"
                    />

                    <Field
                        type="password"
                        name="password"
                        value={data.password}
                        onChange={(event) => setData('password', event.target.value)}
                        placeholder="••••••••"
                        error={errors.password}
                        autoComplete="current-password"
                        className="mb-2.5"
                    />
                </div>

                <div className="mb-3.5 flex w-full justify-between text-xs">
                    <Checkbox
                        checked={remember}
                        onChange={() => setRemember((current) => ! current)}
                        label={<span>Remember me</span>}
                    />

                    <Link href="/ui/forgot-password" className="text-brand">
                        Lost your password?
                    </Link>
                </div>

                <Button type="submit" disabled={processing}>
                    {processing ? 'Signing in…' : 'Sign In'}
                </Button>

                <div className="mt-3 flex gap-1 text-xs">
                    <span>No account?</span>
                    <Link href="/ui/signup" className="text-brand">
                        Register now
                    </Link>
                </div>
            </form>
        </MobileLayout>
    );
}
