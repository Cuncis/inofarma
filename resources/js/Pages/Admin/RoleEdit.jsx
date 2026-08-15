import AdminLayout from '@/Layouts/AdminLayout';
import RoleForm from '@/Components/Admin/RoleForm';
import { roles } from '@/Components/Admin/data';

const granted = [
    'Produk:Lihat',
    'Produk:Tambah',
    'Produk:Ubah',
    'Pesanan:Lihat',
    'Pesanan:Proses',
    'Inventaris:Lihat',
    'Inventaris:Sesuaikan Stok',
    'Laporan:Lihat',
];

export default function RoleEdit() {
    return (
        <AdminLayout
            title="Ubah Peran"
            heading="Ubah Peran"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Peran', href: '/admin/peran' },
                { label: 'Ubah' },
            ]}
        >
            <RoleForm role={roles[1]} granted={granted} submitLabel="Simpan Perubahan" />
        </AdminLayout>
    );
}
