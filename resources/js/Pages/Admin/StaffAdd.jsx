import AdminLayout from '@/Layouts/AdminLayout';
import StaffForm from '@/Components/Admin/StaffForm';

/**
 * @param {{ branches: object[], roles: string[] }} props
 */
export default function StaffAdd({ branches, roles }) {
    return (
        <AdminLayout
            title="Tambah Staf"
            heading="Tambah Staf"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Staf Admin', href: '/admin/staf' },
                { label: 'Tambah' },
            ]}
        >
            <StaffForm branches={branches} roles={roles} submitLabel="Simpan Staf" />
        </AdminLayout>
    );
}
