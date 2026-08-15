import AdminAuthLayout from '@/Layouts/AdminAuthLayout';
import Button from '@/Components/Admin/Button';

export default function Error404() {
    return (
        <AdminAuthLayout title="Halaman Tidak Ditemukan">
            <div className="text-center">
                <p className="font-display text-6xl font-bold text-brand">404</p>

                <h1 className="mt-4 text-lg font-semibold text-admin-heading dark:text-admin-dark-heading">
                    Halaman tidak ditemukan
                </h1>

                <p className="mt-2 text-[13px] leading-relaxed text-admin-muted dark:text-admin-dark-muted">
                    Halaman yang Anda cari mungkin telah dipindahkan atau tidak pernah ada.
                </p>

                <Button href="/admin" icon="solar:widget-5-bold-duotone" className="mt-6">
                    Kembali ke Dasbor
                </Button>
            </div>
        </AdminAuthLayout>
    );
}
