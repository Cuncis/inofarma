import AdminLayout from '@/Layouts/AdminLayout';
import AttributeForm from '@/Components/Admin/AttributeForm';

export default function AttributeAdd({ types }) {
    return (
        <AdminLayout
            title="Tambah Atribut"
            heading="Tambah Atribut"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Atribut', href: '/admin/atribut' },
                { label: 'Tambah' },
            ]}
        >
            <AttributeForm types={types} submitLabel="Simpan Atribut" />
        </AdminLayout>
    );
}
