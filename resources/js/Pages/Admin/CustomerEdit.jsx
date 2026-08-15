import AdminLayout from '@/Layouts/AdminLayout';
import EntityForm from '@/Components/Admin/EntityForm';
import { customers } from '@/Components/Admin/data';

const customer = customers[0];

const fields = [
    { name: 'name', label: 'Nama Lengkap', defaultValue: customer.name },
    { name: 'email', label: 'Email', type: 'email', defaultValue: customer.email },
    { name: 'phone', label: 'Nomor Telepon', type: 'tel', defaultValue: customer.phone },
    { name: 'city', label: 'Kota', defaultValue: customer.city },
    { name: 'address', label: 'Alamat Lengkap', type: 'textarea', defaultValue: 'Jl. Kebon Jeruk Raya No. 27, Jakarta Barat 11530' },
    { name: 'status', label: 'Status', type: 'select', options: ['Aktif', 'Nonaktif'], defaultValue: customer.status },
    { name: 'avatar', label: 'Foto Profil', type: 'upload' },
];

export default function CustomerEdit() {
    return (
        <AdminLayout
            title="Ubah Pelanggan"
            heading="Ubah Pelanggan"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pelanggan', href: '/admin/pelanggan' },
                { label: 'Ubah' },
            ]}
        >
            <EntityForm
                title="Data Pelanggan"
                fields={fields}
                submitLabel="Simpan Perubahan"
                backHref="/admin/pelanggan"
            />
        </AdminLayout>
    );
}
