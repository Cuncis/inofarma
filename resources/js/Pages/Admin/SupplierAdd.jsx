import AdminLayout from '@/Layouts/AdminLayout';
import SupplierForm from '@/Components/Admin/SupplierForm';

export default function SupplierAdd({ statuses }) {
    return (
        <AdminLayout
            title="Tambah Pemasok"
            heading="Tambah Pemasok"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pemasok', href: '/admin/pemasok' },
                { label: 'Tambah' },
            ]}
        >
            <SupplierForm statuses={statuses} submitLabel="Simpan Pemasok" />
        </AdminLayout>
    );
}
