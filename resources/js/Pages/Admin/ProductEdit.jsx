import AdminLayout from '@/Layouts/AdminLayout';
import ProductForm from '@/Components/Admin/ProductForm';

export default function ProductEdit({ product, categories, units, statuses }) {
    return (
        <AdminLayout
            title={`Ubah ${product.name}`}
            heading="Ubah Produk"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Produk', href: '/admin/produk' },
                { label: product.name },
            ]}
        >
            <ProductForm
                product={product}
                categories={categories}
                units={units}
                statuses={statuses}
                submitLabel="Simpan Perubahan"
            />
        </AdminLayout>
    );
}
