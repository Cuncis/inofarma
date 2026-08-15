import AdminLayout from '@/Layouts/AdminLayout';
import EntityForm from '@/Components/Admin/EntityForm';

const fields = [
    { name: 'name', label: 'Nama Atribut', placeholder: 'Dosis' },
    { name: 'slug', label: 'Slug', placeholder: 'dosis' },
    { name: 'type', label: 'Tipe', type: 'select', options: ['Pilihan', 'Teks', 'Angka', 'Warna'] },
    { name: 'group', label: 'Grup', type: 'select', options: ['Umum', 'Farmasi', 'Kemasan'] },
    { name: 'values', label: 'Nilai', type: 'textarea', placeholder: '250mg, 500mg, 1000mg', hint: 'Pisahkan setiap nilai dengan koma.' },
];

export default function AttributeAdd() {
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
            <EntityForm
                title="Informasi Atribut"
                fields={fields}
                submitLabel="Simpan Atribut"
                backHref="/admin/atribut"
            />
        </AdminLayout>
    );
}
