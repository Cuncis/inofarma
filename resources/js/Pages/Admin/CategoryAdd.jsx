import AdminLayout from '@/Layouts/AdminLayout';
import EntityForm from '@/Components/Admin/EntityForm';

const fields = [
    { name: 'name', label: 'Nama Kategori', placeholder: 'Obat Bebas' },
    { name: 'slug', label: 'Slug', placeholder: 'obat-bebas', hint: 'Digunakan pada URL.' },
    { name: 'parent', label: 'Kategori Induk', type: 'select', options: ['Tidak ada', 'Obat', 'Suplemen', 'Alat Kesehatan'] },
    { name: 'status', label: 'Status', type: 'select', options: ['Aktif', 'Nonaktif'] },
    { name: 'description', label: 'Deskripsi', type: 'textarea', placeholder: 'Jelaskan kategori ini...' },
    { name: 'image', label: 'Gambar Kategori', type: 'upload' },
];

export default function CategoryAdd() {
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
            <EntityForm
                title="Informasi Kategori"
                fields={fields}
                submitLabel="Simpan Kategori"
                backHref="/admin/kategori"
            />
        </AdminLayout>
    );
}
