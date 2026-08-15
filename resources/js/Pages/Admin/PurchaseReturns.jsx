import ListPage from '@/Components/Admin/ListPage';
import { purchaseReturns } from '@/Components/Admin/data';

const columns = [
    { key: 'number', label: 'No. Retur' },
    { key: 'supplier', label: 'Pemasok' },
    { key: 'date', label: 'Tanggal' },
    { key: 'items', label: 'Item', align: 'right' },
    { key: 'total', label: 'Nilai', align: 'right', format: 'money' },
    { key: 'reason', label: 'Alasan' },
    { key: 'status', label: 'Status', format: 'status' },
];

export default function PurchaseReturns() {
    return (
        <ListPage
            title="Retur Pembelian"
            heading="Retur Pembelian"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pembelian', href: '/admin/pembelian' },
                { label: 'Retur' },
            ]}
            columns={columns}
            rows={purchaseReturns}
            rowKey={(row) => row.number}
            searchKeys={['number', 'supplier', 'reason']}
            placeholder="Cari nomor retur atau pemasok..."
            empty="Retur tidak ditemukan."
        />
    );
}
