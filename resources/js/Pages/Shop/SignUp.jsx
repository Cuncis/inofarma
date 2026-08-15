import { Link, router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Field from '@/Components/Shop/Field';
import SocialButtons from '@/Components/Shop/SocialButtons';

export default function SignUp() {
    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/verify-phone');
    };

    return (
        <MobileLayout
            title="Daftar"
            header={<AppBar title="Daftar" back="/ui/signin" tone="white" />}
        >
            <form
                onSubmit={submit}
                className="flex-1 overflow-y-auto bg-blush px-[22px] py-[18px]"
            >
                <h1 className="mb-2 text-center font-display text-[22px]">Daftar</h1>

                <p className="mb-[18px] text-center text-[13px] text-muted">
                    Gunakan media sosial atau email Anda
                </p>

                <SocialButtons />

                <Field name="name" placeholder="Kirana Wijaya" className="mb-2.5" />
                <Field
                    type="email"
                    name="email"
                    placeholder="kirana.wijaya@mail.com"
                    className="mb-2.5"
                />
                <Field
                    type="password"
                    name="password"
                    placeholder="Masukkan kata sandi"
                    className="mb-2.5"
                />
                <Field
                    type="password"
                    name="password_confirmation"
                    placeholder="Ulangi kata sandi"
                    className="mb-2.5"
                />

                <Button type="submit">Daftar</Button>

                <div className="mt-2.5 flex justify-center gap-1 text-xs">
                    <span>Sudah punya akun?</span>
                    <Link href="/ui/signin" className="text-brand">
                        Masuk di sini.
                    </Link>
                </div>
            </form>
        </MobileLayout>
    );
}
