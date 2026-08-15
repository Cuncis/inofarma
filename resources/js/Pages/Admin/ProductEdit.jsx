import AdminLayout from '@/Layouts/AdminLayout';
import ProductForm from '@/Components/Admin/ProductForm';
import { products } from '@/Components/Admin/data';

export default function ProductEdit() {
    return (
        <AdminLayout
            title="Ubah Produk"
            heading="Ubah Produk"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Produk', href: '/admin/produk' },
                { label: 'Ubah' },
            ]}
        >
            <ProductForm product={products[0]} submitLabel="Simpan Perubahan" />
        </AdminLayout>
    );
}
