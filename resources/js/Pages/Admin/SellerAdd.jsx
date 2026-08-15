import AdminLayout from '@/Layouts/AdminLayout';
import EntityForm from '@/Components/Admin/EntityForm';

const fields = [
    { name: 'name', label: 'Nama Toko', placeholder: 'Apotek Sehat Bersama' },
    { name: 'owner', label: 'Nama Pemilik', placeholder: 'Kirana Wijaya' },
    { name: 'email', label: 'Email', type: 'email', placeholder: 'apotek@mail.com' },
    { name: 'phone', label: 'Nomor Telepon', type: 'tel', placeholder: '+62 812-3456-7890' },
    { name: 'license', label: 'Nomor Izin Apotek', placeholder: 'SIA/2025/00123' },
    { name: 'city', label: 'Kota', placeholder: 'Jakarta Selatan' },
    { name: 'address', label: 'Alamat Toko', type: 'textarea', placeholder: 'Jl. Jend. Sudirman Kav. 52-53...' },
    { name: 'status', label: 'Status', type: 'select', options: ['Menunggu', 'Terverifikasi', 'Nonaktif'] },
    { name: 'logo', label: 'Logo Toko', type: 'upload' },
];

export default function SellerAdd() {
    return (
        <AdminLayout
            title="Tambah Penjual"
            heading="Tambah Penjual"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Penjual', href: '/admin/penjual' },
                { label: 'Tambah' },
            ]}
        >
            <EntityForm
                title="Data Penjual"
                fields={fields}
                submitLabel="Simpan Penjual"
                backHref="/admin/penjual"
            />
        </AdminLayout>
    );
}
