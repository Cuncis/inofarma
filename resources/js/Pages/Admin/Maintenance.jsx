import AdminAuthLayout from '@/Layouts/AdminAuthLayout';
import Button from '@/Components/Admin/Button';
import Icon from '@/Components/Admin/Icon';

export default function Maintenance() {
    return (
        <AdminAuthLayout title="Dalam Pemeliharaan">
            <div className="text-center">
                <Icon
                    name="solar:settings-minimalistic-broken"
                    size={64}
                    className="mx-auto mb-5 text-brand"
                />

                <h1 className="text-lg font-semibold text-admin-heading dark:text-admin-dark-heading">
                    Situs sedang dalam pemeliharaan
                </h1>

                <p className="mt-2 text-[13px] leading-relaxed text-admin-muted dark:text-admin-dark-muted">
                    Kami sedang melakukan perbaikan terjadwal. Silakan kembali beberapa saat lagi —
                    terima kasih atas kesabaran Anda.
                </p>

                <Button href="/admin" variant="outline" className="mt-6">
                    Muat Ulang
                </Button>
            </div>
        </AdminAuthLayout>
    );
}
