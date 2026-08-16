import AdminLayout from '@/Layouts/AdminLayout';
import BranchForm from '@/Components/Admin/BranchForm';

export default function BranchAdd({ statuses }) {
    return (
        <AdminLayout
            title="Tambah Cabang"
            heading="Tambah Cabang"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Cabang', href: '/admin/cabang' },
                { label: 'Tambah' },
            ]}
        >
            <BranchForm statuses={statuses} submitLabel="Simpan Cabang" />
        </AdminLayout>
    );
}
