import AdminLayout from '@/Layouts/AdminLayout';
import CustomerForm from '@/Components/Admin/CustomerForm';

export default function CustomerAdd({ statuses }) {
    return (
        <AdminLayout
            title="Tambah Pelanggan"
            heading="Tambah Pelanggan"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pelanggan', href: '/admin/pelanggan' },
                { label: 'Tambah' },
            ]}
        >
            <CustomerForm statuses={statuses} submitLabel="Simpan Pelanggan" />
        </AdminLayout>
    );
}
