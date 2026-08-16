import AdminLayout from '@/Layouts/AdminLayout';
import StaffForm from '@/Components/Admin/StaffForm';

/**
 * @param {{ staff: object, branches: object[], roles: string[] }} props
 */
export default function StaffEdit({ staff, branches, roles }) {
    return (
        <AdminLayout
            title="Ubah Staf"
            heading="Ubah Staf"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Staf Admin', href: '/admin/staf' },
                { label: 'Ubah' },
            ]}
        >
            <StaffForm staff={staff} branches={branches} roles={roles} submitLabel="Simpan Perubahan" />
        </AdminLayout>
    );
}
