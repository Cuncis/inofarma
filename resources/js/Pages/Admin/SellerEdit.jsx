import AdminLayout from '@/Layouts/AdminLayout';
import EntityForm from '@/Components/Admin/EntityForm';
import { sellers } from '@/Components/Admin/data';

const seller = sellers[0];

const fields = [
    { name: 'name', label: 'Nama Toko', defaultValue: seller.name },
    { name: 'owner', label: 'Nama Pemilik', defaultValue: seller.owner },
    { name: 'email', label: 'Email', type: 'email', placeholder: 'apotek@mail.com' },
    { name: 'phone', label: 'Nomor Telepon', type: 'tel', placeholder: '+62 812-3456-7890' },
    { name: 'license', label: 'Nomor Izin Apotek', defaultValue: 'SIA/2025/00123' },
    { name: 'city', label: 'Kota', defaultValue: seller.city },
    { name: 'address', label: 'Alamat Toko', type: 'textarea', defaultValue: 'Jl. Jend. Sudirman Kav. 52-53, Jakarta Selatan 12190' },
    { name: 'status', label: 'Status', type: 'select', options: ['Menunggu', 'Terverifikasi', 'Nonaktif'], defaultValue: seller.status },
    { name: 'logo', label: 'Logo Toko', type: 'upload' },
];

export default function SellerEdit() {
    return (
        <AdminLayout
            title="Ubah Penjual"
            heading="Ubah Penjual"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Penjual', href: '/admin/penjual' },
                { label: 'Ubah' },
            ]}
        >
            <EntityForm
                title="Data Penjual"
                fields={fields}
                submitLabel="Simpan Perubahan"
                backHref="/admin/penjual"
            />
        </AdminLayout>
    );
}
