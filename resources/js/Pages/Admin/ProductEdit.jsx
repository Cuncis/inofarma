import AdminLayout from '@/Layouts/AdminLayout';
import ProductForm from '@/Components/Admin/ProductForm';
import ProductImageManager from '@/Components/Admin/ProductImageManager';

export default function ProductEdit({
    product,
    categories,
    sellers,
    units,
    statuses,
    drugClasses,
    storageConditions,
}) {
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
            <div className="mb-5">
                <ProductImageManager product={product} />
            </div>

            <ProductForm
                product={product}
                categories={categories}
                sellers={sellers}
                units={units}
                statuses={statuses}
                drugClasses={drugClasses}
                storageConditions={storageConditions}
                submitLabel="Simpan Perubahan"
            />
        </AdminLayout>
    );
}
