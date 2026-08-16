import AdminLayout from '@/Layouts/AdminLayout';
import BranchForm from '@/Components/Admin/BranchForm';

export default function BranchEdit({ branch, statuses }) {
    return (
        <AdminLayout
            title={`Ubah ${branch.name}`}
            heading={`Ubah ${branch.name}`}
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Cabang', href: '/admin/cabang' },
                { label: branch.name },
            ]}
        >
            <BranchForm branch={branch} statuses={statuses} submitLabel="Simpan Perubahan" />
        </AdminLayout>
    );
}
