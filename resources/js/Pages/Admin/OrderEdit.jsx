import AdminLayout from '@/Layouts/AdminLayout';
import OrderForm from '@/Components/Admin/OrderForm';

export default function OrderEdit({ order, customers, products, branches, statuses, payments, fulfilments }) {
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
                branches={branches}
                statuses={statuses}
                payments={payments}
                fulfilments={fulfilments}
                submitLabel="Simpan Perubahan"
            />
        </AdminLayout>
    );
}
