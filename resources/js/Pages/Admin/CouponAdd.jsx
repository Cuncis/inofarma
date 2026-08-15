import AdminLayout from '@/Layouts/AdminLayout';
import EntityForm from '@/Components/Admin/EntityForm';

const fields = [
    { name: 'code', label: 'Kode Kupon', placeholder: 'HEMAT15' },
    { name: 'type', label: 'Tipe Diskon', type: 'select', options: ['Persentase', 'Nominal', 'Gratis Ongkir'] },
    { name: 'value', label: 'Nilai Diskon', placeholder: '15' },
    { name: 'minimum', label: 'Minimum Belanja (Rp)', type: 'number', placeholder: '100000' },
    { name: 'quota', label: 'Kuota Penggunaan', type: 'number', placeholder: '500' },
    { name: 'expires', label: 'Berlaku Sampai', type: 'date' },
    { name: 'status', label: 'Status', type: 'select', options: ['Aktif', 'Nonaktif'] },
    { name: 'scope', label: 'Berlaku Untuk', type: 'select', options: ['Semua Produk', 'Kategori Tertentu', 'Produk Tertentu'] },
    { name: 'description', label: 'Keterangan', type: 'textarea', placeholder: 'Syarat dan ketentuan kupon...' },
];

export default function CouponAdd() {
    return (
        <AdminLayout
            title="Tambah Kupon"
            heading="Tambah Kupon"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Kupon', href: '/admin/kupon' },
                { label: 'Tambah' },
            ]}
        >
            <EntityForm
                title="Informasi Kupon"
                fields={fields}
                submitLabel="Simpan Kupon"
                backHref="/admin/kupon"
            />
        </AdminLayout>
    );
}
