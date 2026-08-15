import AdminLayout from '@/Layouts/AdminLayout';
import CategoryForm from '@/Components/Admin/CategoryForm';

export default function CategoryAdd({ statuses }) {
    return (
        <AdminLayout
            title="Tambah Kategori"
            heading="Tambah Kategori"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Kategori', href: '/admin/kategori' },
                { label: 'Tambah' },
            ]}
        >
            <CategoryForm statuses={statuses} submitLabel="Simpan Kategori" />
        </AdminLayout>
    );
}
