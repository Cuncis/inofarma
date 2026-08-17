import AdminLayout from '@/Layouts/AdminLayout';
import CouponForm from '@/Components/Admin/CouponForm';

export default function CouponAdd({ types, statuses, branches }) {
    return (
        <AdminLayout
            title="Tambah Kupon"
            heading="Tambah Kupon"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Kupon', href: '/admin/kupon' },
                { label: 'Tambah' },
            ]}
        >
            <CouponForm types={types} statuses={statuses} branches={branches} submitLabel="Simpan Kupon" />
        </AdminLayout>
    );
}
