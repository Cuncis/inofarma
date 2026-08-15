import AdminLayout from '@/Layouts/AdminLayout';
import SellerForm from '@/Components/Admin/SellerForm';

export default function SellerAdd({ statuses }) {
    return (
        <AdminLayout
            title="Tambah Penjual"
            heading="Tambah Penjual"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Penjual', href: '/admin/penjual' },
                { label: 'Tambah' },
            ]}
        >
            <SellerForm statuses={statuses} submitLabel="Simpan Penjual" />
        </AdminLayout>
    );
}
