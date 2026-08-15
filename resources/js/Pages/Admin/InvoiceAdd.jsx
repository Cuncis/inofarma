import AdminLayout from '@/Layouts/AdminLayout';
import EntityForm from '@/Components/Admin/EntityForm';
import { customers } from '@/Components/Admin/data';

const fields = [
    { name: 'number', label: 'Nomor Faktur', placeholder: 'INV-2025-0452' },
    { name: 'customer', label: 'Pelanggan', type: 'select', options: customers.map((item) => item.name) },
    { name: 'issued', label: 'Tanggal Terbit', type: 'date' },
    { name: 'due', label: 'Jatuh Tempo', type: 'date' },
    { name: 'payment', label: 'Metode Pembayaran', type: 'select', options: ['Transfer Bank', 'GoPay', 'OVO', 'DANA', 'Tunai'] },
    { name: 'status', label: 'Status', type: 'select', options: ['Belum Bayar', 'Lunas', 'Jatuh Tempo'] },
    { name: 'notes', label: 'Catatan', type: 'textarea', placeholder: 'Catatan pada faktur...' },
];

export default function InvoiceAdd() {
    return (
        <AdminLayout
            title="Buat Faktur"
            heading="Buat Faktur"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Faktur', href: '/admin/faktur' },
                { label: 'Buat' },
            ]}
        >
            <EntityForm
                title="Informasi Faktur"
                fields={fields}
                submitLabel="Simpan Faktur"
                backHref="/admin/faktur"
            />
        </AdminLayout>
    );
}
