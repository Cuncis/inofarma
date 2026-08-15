import AdminLayout from '@/Layouts/AdminLayout';
import Button from '@/Components/Admin/Button';
import Icon from '@/Components/Admin/Icon';

export default function Error404Alt() {
    return (
        <AdminLayout title="Halaman Tidak Ditemukan">
            <div className="flex min-h-[60vh] flex-col items-center justify-center rounded-xl border border-admin-border bg-admin-card p-10 text-center shadow-card dark:border-admin-dark-border dark:bg-admin-dark-card">
                <Icon
                    name="solar:danger-triangle-broken"
                    size={64}
                    className="mb-5 text-warning"
                />

                <p className="font-display text-5xl font-bold text-brand">404</p>

                <h1 className="mt-3 text-lg font-semibold text-admin-heading dark:text-admin-dark-heading">
                    Halaman tidak ditemukan
                </h1>

                <p className="mt-2 max-w-sm text-[13px] leading-relaxed text-admin-muted dark:text-admin-dark-muted">
                    Tautan yang Anda buka sudah tidak berlaku. Periksa kembali alamatnya atau
                    kembali ke dasbor.
                </p>

                <Button href="/admin" className="mt-6">
                    Kembali ke Dasbor
                </Button>
            </div>
        </AdminLayout>
    );
}
