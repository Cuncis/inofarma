import AdminLayout from '@/Layouts/AdminLayout';
import OrderForm from '@/Components/Admin/OrderForm';

export default function OrderEdit({ order, customers, products, statuses, payments }) {
    return (
        <AdminLayout
            title={`Ubah Pesanan #${order.id}`}
            heading={`Ubah Pesanan #${order.id}`}
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pesanan', href: '/admin/pesanan' },
                { label: `#${order.id}` },
            ]}
        >
            <OrderForm
                order={order}
                customers={customers}
                products={products}
                statuses={statuses}
                payments={payments}
                submitLabel="Simpan Perubahan"
            />
        </AdminLayout>
    );
}
