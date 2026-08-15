import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Admin/Card';

export default function Starter() {
    return (
        <AdminLayout
            title="Halaman Awal"
            heading="Halaman Awal"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Halaman Awal' }]}
        >
            <Card>
                <p className="text-[13px] leading-relaxed text-admin-body dark:text-admin-dark-body">
                    Halaman kosong siap pakai. Salin berkas ini sebagai titik awal ketika membuat
                    layar admin baru — kerangka layout, judul, dan remah roti sudah terpasang.
                </p>
            </Card>
        </AdminLayout>
    );
}
