import { useEffect, useState } from 'react';
import AdminAuthLayout from '@/Layouts/AdminAuthLayout';
import Button from '@/Components/Admin/Button';
import { Input } from '@/Components/Admin/Form';

/** Launch date the countdown ticks towards. */
const launchesAt = new Date('2026-01-01T00:00:00+07:00');

/**
 * @returns {{ hari: number, jam: number, menit: number, detik: number }}
 */
function remaining() {
    const delta = Math.max(launchesAt.getTime() - Date.now(), 0);

    return {
        hari: Math.floor(delta / 86400000),
        jam: Math.floor(delta / 3600000) % 24,
        menit: Math.floor(delta / 60000) % 60,
        detik: Math.floor(delta / 1000) % 60,
    };
}

export default function ComingSoon() {
    const [left, setLeft] = useState(remaining);

    useEffect(() => {
        const timer = window.setInterval(() => setLeft(remaining()), 1000);

        return () => window.clearInterval(timer);
    }, []);

    return (
        <AdminAuthLayout title="Segera Hadir" width="max-w-lg">
            <div className="text-center">
                <h1 className="text-lg font-semibold text-admin-heading dark:text-admin-dark-heading">
                    Segera hadir
                </h1>

                <p className="mt-2 text-[13px] text-admin-muted dark:text-admin-dark-muted">
                    Kami sedang menyiapkan sesuatu yang baru. Tinggalkan email Anda untuk
                    mendapatkan kabar pertama.
                </p>

                <div className="my-7 grid grid-cols-4 gap-2">
                    {Object.entries(left).map(([unit, value]) => (
                        <div
                            key={unit}
                            className="rounded-lg bg-admin-hover py-3 dark:bg-admin-dark-hover"
                        >
                            <p className="text-xl font-bold text-admin-heading dark:text-admin-dark-heading">
                                {String(value).padStart(2, '0')}
                            </p>
                            <p className="mt-0.5 text-[10px] uppercase tracking-wider text-admin-muted dark:text-admin-dark-muted">
                                {unit}
                            </p>
                        </div>
                    ))}
                </div>

                <form
                    onSubmit={(event) => event.preventDefault()}
                    className="flex flex-col gap-2 sm:flex-row"
                >
                    <Input type="email" placeholder="Alamat email Anda" className="flex-1" />
                    <Button type="submit">Beri Tahu Saya</Button>
                </form>
            </div>
        </AdminAuthLayout>
    );
}
