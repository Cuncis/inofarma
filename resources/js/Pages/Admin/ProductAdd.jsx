import AdminLayout from '@/Layouts/AdminLayout';
import ProductForm from '@/Components/Admin/ProductForm';

export default function ProductAdd() {
    return (
        <AdminLayout
            title="Tambah Produk"
            heading="Tambah Produk"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Produk', href: '/admin/produk' },
                { label: 'Tambah' },
            ]}
        >
            <ProductForm submitLabel="Simpan Produk" />
        </AdminLayout>
    );
}
