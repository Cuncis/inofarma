import { Link, useForm } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Field from '@/Components/Shop/Field';
import SocialButtons from '@/Components/Shop/SocialButtons';

export default function SignUp() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (event) => {
        event.preventDefault();

        post('/ui/daftar');
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

                <Field
                    name="name"
                    value={data.name}
                    onChange={(event) => setData('name', event.target.value)}
                    placeholder="Kirana Wijaya"
                    error={errors.name}
                    className="mb-2.5"
                />
                <Field
                    type="email"
                    name="email"
                    value={data.email}
                    onChange={(event) => setData('email', event.target.value)}
                    placeholder="kirana.wijaya@mail.com"
                    error={errors.email}
                    className="mb-2.5"
                />
                <Field
                    type="password"
                    name="password"
                    value={data.password}
                    onChange={(event) => setData('password', event.target.value)}
                    placeholder="Masukkan kata sandi"
                    error={errors.password}
                    className="mb-2.5"
                />
                <Field
                    type="password"
                    name="password_confirmation"
                    value={data.password_confirmation}
                    onChange={(event) => setData('password_confirmation', event.target.value)}
                    placeholder="Ulangi kata sandi"
                    className="mb-2.5"
                />

                <Button type="submit" disabled={processing}>
                    {processing ? 'Memproses…' : 'Daftar'}
                </Button>

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
