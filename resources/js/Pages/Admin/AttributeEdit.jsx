import AdminLayout from '@/Layouts/AdminLayout';
import AttributeForm from '@/Components/Admin/AttributeForm';

export default function AttributeEdit({ attribute, types }) {
    return (
        <AdminLayout
            title={`Ubah ${attribute.name}`}
            heading="Ubah Atribut"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Atribut', href: '/admin/atribut' },
                { label: attribute.name },
            ]}
        >
            <AttributeForm attribute={attribute} types={types} submitLabel="Simpan Perubahan" />
        </AdminLayout>
    );
}
