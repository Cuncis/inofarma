import AdminLayout from '@/Layouts/AdminLayout';
import OrderForm from '@/Components/Admin/OrderForm';

export default function OrderAdd({ customers, products, statuses, payments }) {
    return (
        <AdminLayout
            title="Buat Pesanan"
            heading="Buat Pesanan"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pesanan', href: '/admin/pesanan' },
                { label: 'Buat' },
            ]}
        >
            <OrderForm
                customers={customers}
                products={products}
                statuses={statuses}
                payments={payments}
                submitLabel="Simpan Pesanan"
            />
        </AdminLayout>
    );
}
