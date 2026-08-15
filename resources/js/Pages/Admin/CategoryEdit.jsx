import AdminLayout from '@/Layouts/AdminLayout';
import CategoryForm from '@/Components/Admin/CategoryForm';

export default function CategoryEdit({ category, statuses }) {
    return (
        <AdminLayout
            title={`Ubah ${category.name}`}
            heading="Ubah Kategori"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Kategori', href: '/admin/kategori' },
                { label: category.name },
            ]}
        >
            <CategoryForm
                category={category}
                statuses={statuses}
                submitLabel="Simpan Perubahan"
            />
        </AdminLayout>
    );
}
