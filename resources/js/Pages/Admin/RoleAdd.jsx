import AdminLayout from '@/Layouts/AdminLayout';
import RoleForm from '@/Components/Admin/RoleForm';

export default function RoleAdd() {
    return (
        <AdminLayout
            title="Tambah Peran"
            heading="Tambah Peran"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Peran', href: '/admin/peran' },
                { label: 'Tambah' },
            ]}
        >
            <RoleForm submitLabel="Simpan Peran" />
        </AdminLayout>
    );
}
