import { Link, useForm } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import Button from '@/Components/Shop/Button';
import Checkbox from '@/Components/Shop/Checkbox';
import { asset } from '@/Components/Shop/data';
import Field from '@/Components/Shop/Field';

export default function SignIn() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (event) => {
        event.preventDefault();

        post('/ui/signin');
    };

    return (
        <MobileLayout title="Masuk" background="bg-canvas">
            <form
                onSubmit={submit}
                autoComplete="off"
                className="flex flex-1 flex-col items-center justify-center overflow-y-auto px-[22px] py-6"
            >
                <img src={asset.logo('white')} alt="Inofarma" className="mb-6 h-10 w-auto" />

                <h1 className="mb-2 font-display text-2xl text-brand">Masuk</h1>

                <p className="mb-5 text-center text-[13px] text-muted">
                    Masuk menggunakan email Anda
                </p>

                <div className="w-full">
                    <Field
                        type="text"
                        name="email"
                        label="Email/No. Telepon"
                        value={data.email}
                        onChange={(event) => setData('email', event.target.value)}
                        placeholder="Contoh: kirana.wijaya@mail.com atau 081234567890"
                        error={errors.email}
                        autoComplete="off"
                        className="mb-2.5"
                    />

                    <Field
                        type="password"
                        name="password"
                        label="Kata Sandi"
                        value={data.password}
                        onChange={(event) => setData('password', event.target.value)}
                        placeholder="••••••••"
                        error={errors.password}
                        autoComplete="off"
                        className="mb-2.5"
                    />
                </div>

                <div className="mb-3.5 flex w-full justify-between text-xs">
                    <Checkbox
                        checked={data.remember}
                        onChange={() => setData('remember', ! data.remember)}
                        label={<span>Ingat saya</span>}
                    />

                    <Link href="/ui/forgot-password" className="text-brand">
                        Lupa kata sandi?
                    </Link>
                </div>

                <Button type="submit" disabled={processing}>
                    {processing ? 'Memproses…' : 'Masuk'}
                </Button>

                <div className="mt-3 flex gap-1 text-xs">
                    <span>Belum punya akun?</span>
                    <Link href="/ui/signup" className="text-brand">
                        Daftar sekarang
                    </Link>
                </div>
            </form>
        </MobileLayout>
    );
}
