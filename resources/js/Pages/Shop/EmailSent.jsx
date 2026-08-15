import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import { asset } from '@/Components/Shop/data';

export default function EmailSent() {
    return (
        <MobileLayout
            title="Email Terkirim"
            header={
                <AppBar title="Atur Ulang Sandi" back="/ui/forgot-password" tone="white" />
            }
        >
            <div className="flex flex-1 flex-col items-center justify-center overflow-y-auto bg-blush p-6 text-center">
                <img
                    src={asset.other('04')}
                    alt=""
                    className="mx-auto mb-4 h-[180px] w-[180px] object-contain"
                />

                <h2 className="mb-2.5 font-display text-[22px]">Email Terkirim!</h2>

                <p className="mb-[22px] text-[13px] leading-relaxed text-muted">
                    Kami telah mengirim petunjuk pemulihan
                    <br />
                    kata sandi ke email Anda.
                </p>

                <Button href="/ui/new-password">Oke, Mengerti!</Button>

                <div className="mt-2.5 flex gap-1 text-xs">
                    <span>Tidak menerima email?</span>
                    <Link href="/ui/forgot-password" className="text-brand">
                        Kirim ulang
                    </Link>
                </div>
            </div>
        </MobileLayout>
    );
}
