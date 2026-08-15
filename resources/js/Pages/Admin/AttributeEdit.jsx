import AdminLayout from '@/Layouts/AdminLayout';
import EntityForm from '@/Components/Admin/EntityForm';
import { attributes } from '@/Components/Admin/data';

const attribute = attributes[0];

const fields = [
    { name: 'name', label: 'Nama Atribut', defaultValue: attribute.name },
    { name: 'slug', label: 'Slug', defaultValue: attribute.slug },
    { name: 'type', label: 'Tipe', type: 'select', options: ['Pilihan', 'Teks', 'Angka', 'Warna'], defaultValue: attribute.type },
    { name: 'group', label: 'Grup', type: 'select', options: ['Umum', 'Farmasi', 'Kemasan'] },
    { name: 'values', label: 'Nilai', type: 'textarea', defaultValue: attribute.values.join(', '), hint: 'Pisahkan setiap nilai dengan koma.' },
];

export default function AttributeEdit() {
    return (
        <AdminLayout
            title="Ubah Atribut"
            heading="Ubah Atribut"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Atribut', href: '/admin/atribut' },
                { label: 'Ubah' },
            ]}
        >
            <EntityForm
                title="Informasi Atribut"
                fields={fields}
                submitLabel="Simpan Perubahan"
                backHref="/admin/atribut"
            />
        </AdminLayout>
    );
}
