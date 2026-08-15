import ListPage from '@/Components/Admin/ListPage';
import { receivedOrders } from '@/Components/Admin/data';

const columns = [
    { key: 'number', label: 'No. PO' },
    { key: 'supplier', label: 'Pemasok' },
    { key: 'received', label: 'Diterima' },
    { key: 'items', label: 'Item', align: 'right' },
    { key: 'warehouse', label: 'Gudang' },
    { key: 'status', label: 'Status', format: 'status' },
];

export default function InventoryReceivedOrders() {
    return (
        <ListPage
            title="Pesanan Masuk"
            heading="Pesanan Masuk"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Inventaris' },
                { label: 'Pesanan Masuk' },
            ]}
            columns={columns}
            rows={receivedOrders}
            rowKey={(row) => row.number}
            searchKeys={['number', 'supplier', 'warehouse']}
            placeholder="Cari nomor PO atau pemasok..."
            empty="Pesanan masuk tidak ditemukan."
        />
    );
}
