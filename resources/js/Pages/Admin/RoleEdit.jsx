import AdminLayout from '@/Layouts/AdminLayout';
import RoleForm from '@/Components/Admin/RoleForm';

/**
 * @param {{ role: object, permissionGroups: Record<string, string[]> }} props
 */
export default function RoleEdit({ role, permissionGroups }) {
    return (
        <AdminLayout
            title="Ubah Peran"
            heading="Ubah Peran"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Peran', href: '/admin/peran' },
                { label: 'Ubah' },
            ]}
        >
            <RoleForm role={role} permissionGroups={permissionGroups} submitLabel="Simpan Perubahan" />
        </AdminLayout>
    );
}
