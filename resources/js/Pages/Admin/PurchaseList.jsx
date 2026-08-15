import ListPage from '@/Components/Admin/ListPage';
import Button from '@/Components/Admin/Button';
import { purchases } from '@/Components/Admin/data';

const columns = [
    { key: 'number', label: 'No. PO' },
    { key: 'supplier', label: 'Pemasok' },
    { key: 'date', label: 'Tanggal' },
    { key: 'items', label: 'Item', align: 'right' },
    { key: 'total', label: 'Total', align: 'right', format: 'money' },
    { key: 'status', label: 'Status', format: 'status' },
];

export default function PurchaseList() {
    return (
        <ListPage
            title="Daftar Pembelian"
            heading="Pembelian"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Pembelian' }]}
            columns={columns}
            rows={purchases}
            rowKey={(row) => row.number}
            searchKeys={['number', 'supplier']}
            placeholder="Cari nomor PO atau pemasok..."
            empty="Pembelian tidak ditemukan."
            actions={
                <Button href="/admin/pembelian/order" icon="solar:add-circle-broken" size="sm">
                    Buat Order
                </Button>
            }
        />
    );
}
