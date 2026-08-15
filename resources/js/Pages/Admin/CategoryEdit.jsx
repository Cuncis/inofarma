import AdminLayout from '@/Layouts/AdminLayout';
import EntityForm from '@/Components/Admin/EntityForm';
import { categories } from '@/Components/Admin/data';

const category = categories[0];

const fields = [
    { name: 'name', label: 'Nama Kategori', defaultValue: category.name },
    { name: 'slug', label: 'Slug', defaultValue: category.slug, hint: 'Digunakan pada URL.' },
    { name: 'parent', label: 'Kategori Induk', type: 'select', options: ['Tidak ada', 'Obat', 'Suplemen', 'Alat Kesehatan'] },
    { name: 'status', label: 'Status', type: 'select', options: ['Aktif', 'Nonaktif'], defaultValue: category.status },
    { name: 'description', label: 'Deskripsi', type: 'textarea', placeholder: 'Jelaskan kategori ini...' },
    { name: 'image', label: 'Gambar Kategori', type: 'upload' },
];

export default function CategoryEdit() {
    return (
        <AdminLayout
            title="Ubah Kategori"
            heading="Ubah Kategori"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Kategori', href: '/admin/kategori' },
                { label: 'Ubah' },
            ]}
        >
            <EntityForm
                title="Informasi Kategori"
                fields={fields}
                submitLabel="Simpan Perubahan"
                backHref="/admin/kategori"
            />
        </AdminLayout>
    );
}
