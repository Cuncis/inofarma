import AdminLayout from '@/Layouts/AdminLayout';
import RoleForm from '@/Components/Admin/RoleForm';

/**
 * @param {{ permissionGroups: Record<string, string[]> }} props
 */
export default function RoleAdd({ permissionGroups }) {
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
            <RoleForm permissionGroups={permissionGroups} submitLabel="Simpan Peran" />
        </AdminLayout>
    );
}
