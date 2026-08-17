import AdminLayout from '@/Layouts/AdminLayout';
import CouponForm from '@/Components/Admin/CouponForm';

export default function CouponEdit({ coupon, types, statuses, branches }) {
    return (
        <AdminLayout
            title={`Ubah ${coupon.code}`}
            heading="Ubah Kupon"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Kupon', href: '/admin/kupon' },
                { label: coupon.code },
            ]}
        >
            <CouponForm
                coupon={coupon}
                types={types}
                statuses={statuses}
                branches={branches}
                submitLabel="Simpan Perubahan"
            />
        </AdminLayout>
    );
}
