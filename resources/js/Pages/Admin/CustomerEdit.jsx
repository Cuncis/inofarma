import AdminLayout from '@/Layouts/AdminLayout';
import CustomerForm from '@/Components/Admin/CustomerForm';

export default function CustomerEdit({ customer, statuses }) {
    return (
        <AdminLayout
            title={`Ubah ${customer.name}`}
            heading="Ubah Pelanggan"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pelanggan', href: '/admin/pelanggan' },
                { label: customer.name },
            ]}
        >
            <CustomerForm
                customer={customer}
                statuses={statuses}
                submitLabel="Simpan Perubahan"
            />
        </AdminLayout>
    );
}
