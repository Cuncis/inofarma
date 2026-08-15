import AdminLayout from '@/Layouts/AdminLayout';
import EntityForm from '@/Components/Admin/EntityForm';

const fields = [
    { name: 'name', label: 'Nama Lengkap', placeholder: 'Kirana Wijaya' },
    { name: 'email', label: 'Email', type: 'email', placeholder: 'kirana@mail.com' },
    { name: 'phone', label: 'Nomor Telepon', type: 'tel', placeholder: '+62 812-3456-7890' },
    { name: 'city', label: 'Kota', placeholder: 'Jakarta Barat' },
    { name: 'address', label: 'Alamat Lengkap', type: 'textarea', placeholder: 'Jl. Kebon Jeruk Raya No. 27...' },
    { name: 'status', label: 'Status', type: 'select', options: ['Aktif', 'Nonaktif'] },
    { name: 'avatar', label: 'Foto Profil', type: 'upload' },
];

export default function CustomerAdd() {
    return (
        <AdminLayout
            title="Tambah Pelanggan"
            heading="Tambah Pelanggan"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pelanggan', href: '/admin/pelanggan' },
                { label: 'Tambah' },
            ]}
        >
            <EntityForm
                title="Data Pelanggan"
                fields={fields}
                submitLabel="Simpan Pelanggan"
                backHref="/admin/pelanggan"
            />
        </AdminLayout>
    );
}
