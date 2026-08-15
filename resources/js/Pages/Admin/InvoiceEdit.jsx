import AdminLayout from '@/Layouts/AdminLayout';
import EntityForm from '@/Components/Admin/EntityForm';
import { customers, invoices } from '@/Components/Admin/data';

const invoice = invoices[0];

const fields = [
    { name: 'number', label: 'Nomor Faktur', defaultValue: invoice.number },
    { name: 'customer', label: 'Pelanggan', type: 'select', options: customers.map((item) => item.name), defaultValue: invoice.customer },
    { name: 'issued', label: 'Tanggal Terbit', type: 'date' },
    { name: 'due', label: 'Jatuh Tempo', type: 'date' },
    { name: 'payment', label: 'Metode Pembayaran', type: 'select', options: ['Transfer Bank', 'GoPay', 'OVO', 'DANA', 'Tunai'] },
    { name: 'status', label: 'Status', type: 'select', options: ['Belum Bayar', 'Lunas', 'Jatuh Tempo'], defaultValue: invoice.status },
    { name: 'notes', label: 'Catatan', type: 'textarea', placeholder: 'Catatan pada faktur...' },
];

export default function InvoiceEdit() {
    return (
        <AdminLayout
            title="Ubah Faktur"
            heading="Ubah Faktur"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Faktur', href: '/admin/faktur' },
                { label: 'Ubah' },
            ]}
        >
            <EntityForm
                title="Informasi Faktur"
                fields={fields}
                submitLabel="Simpan Perubahan"
                backHref="/admin/faktur"
            />
        </AdminLayout>
    );
}
