import AdminLayout from '@/Layouts/AdminLayout';
import SellerForm from '@/Components/Admin/SellerForm';

export default function SellerEdit({ seller, statuses }) {
    return (
        <AdminLayout
            title={`Ubah ${seller.name}`}
            heading="Ubah Penjual"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Penjual', href: '/admin/penjual' },
                { label: seller.name },
            ]}
        >
            <SellerForm seller={seller} statuses={statuses} submitLabel="Simpan Perubahan" />
        </AdminLayout>
    );
}
