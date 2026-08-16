import AdminLayout from '@/Layouts/AdminLayout';
import OrderForm from '@/Components/Admin/OrderForm';

export default function OrderAdd({ customers, products, branches, statuses, payments, fulfilments }) {
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
                branches={branches}
                statuses={statuses}
                payments={payments}
                fulfilments={fulfilments}
                submitLabel="Simpan Pesanan"
            />
        </AdminLayout>
    );
}
