import AdminLayout from '@/Layouts/AdminLayout';
import SupplierForm from '@/Components/Admin/SupplierForm';

export default function SupplierEdit({ supplier, statuses }) {
    return (
        <AdminLayout
            title={`Ubah ${supplier.name}`}
            heading="Ubah Pemasok"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pemasok', href: '/admin/pemasok' },
                { label: supplier.name },
            ]}
        >
            <SupplierForm supplier={supplier} statuses={statuses} submitLabel="Simpan Perubahan" />
        </AdminLayout>
    );
}
