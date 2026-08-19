import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Table from '@/Components/Admin/Table';
import { money, statusTone } from '@/Components/Admin/data';

export default function ProductDetail({ product }) {
    const [tab, setTab] = useState('spesifikasi');
    const branches = product.branches ?? [];

    const specs = [
        { label: 'SKU', value: product.id },
        { label: 'Kategori', value: product.category },
        { label: 'Satuan', value: product.unit },
        { label: 'Butuh Resep', value: product.prescription ? 'Ya' : 'Tidak' },
        { label: 'Harga Coret', value: product.oldPrice ? money(product.oldPrice) : '—' },
        { label: 'Terjual', value: `${product.sold}` },
    ];

    const pharma = [
        { label: 'Golongan Obat', value: product.drugClass },
        { label: 'Nomor Izin Edar (NIE)', value: product.nie },
        { label: 'Komposisi', value: product.composition },
        { label: 'Indikasi', value: product.indication },
        { label: 'Aturan Pakai', value: product.dosage },
        { label: 'Efek Samping', value: product.sideEffects },
        { label: 'Peringatan', value: product.warning },
        { label: 'Produsen', value: product.manufacturer },
        { label: 'Kondisi Penyimpanan', value: product.storage },
        { label: 'Batas Pembelian', value: product.maxQtyPerOrder ? `${product.maxQtyPerOrder} per transaksi` : '—' },
        { label: 'Berat', value: product.weightGrams ? `${product.weightGrams} gram` : '—' },
        {
            label: 'Dimensi (P×L×T)',
            value: product.lengthCm || product.widthCm || product.heightCm
                ? `${product.lengthCm}×${product.widthCm}×${product.heightCm} cm`
                : '—',
        },
    ].map((spec) => ({ ...spec, value: spec.value || '—' }));

    return (
        <AdminLayout
            title="Detail Produk"
            heading={product.name}
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Produk', href: '/admin/produk' },
                { label: 'Detail' },
            ]}
            actions={
                <>
                    <Button href={`/admin/produk/${product.id}/ubah`} variant="outline" size="sm" icon="solar:pen-2-broken">
                        Ubah
                    </Button>
                    <Button href="/admin/produk" size="sm">
                        Kembali
                    </Button>
                </>
            }
        >
            <div className="grid gap-5 lg:grid-cols-3">
                <Card className="lg:col-span-1">
                    <div className="flex h-56 items-center justify-center rounded-lg bg-admin-hover p-6 dark:bg-admin-dark-hover">
                        <img
                            src={product.image}
                            alt={product.name}
                            className="h-full max-w-full object-contain"
                        />
                    </div>

                    {product.images.length > 1 ? (
                        <div className="mt-2 flex gap-1.5 overflow-x-auto">
                            {product.images.map((image) => (
                                <img
                                    key={image.id}
                                    src={image.thumb}
                                    alt=""
                                    className={`h-12 w-12 shrink-0 rounded object-cover ${
                                        image.isPrimary ? 'ring-2 ring-brand' : ''
                                    }`}
                                />
                            ))}
                        </div>
                    ) : null}

                    <div className="mt-5">
                        <div className="flex flex-wrap gap-1.5">
                            <Badge tone={statusTone(product.status)}>{product.status}</Badge>
                            <Badge tone={statusTone(product.stockStatus)}>{product.stockStatus}</Badge>
                        </div>

                        <h2 className="mt-2 text-lg font-semibold text-admin-heading dark:text-admin-dark-heading">
                            {product.name}
                        </h2>

                        <p className="mt-1 text-2xl font-bold text-brand">{money(product.price)}</p>

                        <dl className="mt-4 grid grid-cols-2 gap-3 border-t border-admin-border pt-4 dark:border-admin-dark-border">
                            <div>
                                <dt className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                    Stok ({branches.length} cabang)
                                </dt>
                                <dd className="text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {product.stock}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-xs text-admin-muted dark:text-admin-dark-muted">Terjual</dt>
                                <dd className="text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {product.sold}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </Card>

                <div className="lg:col-span-2">
                    <Card bodyClassName="p-0">
                        <div className="flex border-b border-admin-border dark:border-admin-dark-border">
                            {['spesifikasi', 'farmasi', 'stok cabang'].map((name) => (
                                <button
                                    key={name}
                                    type="button"
                                    onClick={() => setTab(name)}
                                    className={`px-5 py-3.5 text-[13px] font-semibold capitalize transition-colors ${
                                        tab === name
                                            ? 'border-b-2 border-brand text-brand'
                                            : 'text-admin-muted hover:text-admin-heading dark:text-admin-dark-muted'
                                    }`}
                                >
                                    {name}
                                </button>
                            ))}
                        </div>

                        {tab === 'spesifikasi' ? (
                            <dl className="divide-y divide-admin-border dark:divide-admin-dark-border">
                                {specs.map((spec) => (
                                    <div key={spec.label} className="flex justify-between px-5 py-3.5">
                                        <dt className="text-[13px] text-admin-muted dark:text-admin-dark-muted">
                                            {spec.label}
                                        </dt>
                                        <dd className="text-[13px] font-medium text-admin-heading dark:text-admin-dark-heading">
                                            {spec.value}
                                        </dd>
                                    </div>
                                ))}
                            </dl>
                        ) : tab === 'farmasi' ? (
                            <dl className="divide-y divide-admin-border dark:divide-admin-dark-border">
                                {pharma.map((spec) => (
                                    <div key={spec.label} className="flex justify-between gap-4 px-5 py-3.5">
                                        <dt className="shrink-0 text-[13px] text-admin-muted dark:text-admin-dark-muted">
                                            {spec.label}
                                        </dt>
                                        <dd className="text-right text-[13px] font-medium text-admin-heading dark:text-admin-dark-heading">
                                            {spec.value}
                                        </dd>
                                    </div>
                                ))}
                            </dl>
                        ) : (
                            /*
                                Sebaran stok yang sesungguhnya, satu baris per
                                cabang. Baca saja di sini — penyesuaian jumlah
                                dilakukan dari layar cabang, supaya setiap
                                perubahan stok punya cabang dan alasan yang jelas.
                            */
                            <Table
                                columns={[
                                    { key: 'name', label: 'Cabang' },
                                    { key: 'kota', label: 'Kota' },
                                    { key: 'quantity', label: 'Fisik', align: 'right' },
                                    { key: 'reserved', label: 'Dipesan', align: 'right' },
                                    { key: 'available', label: 'Tersedia', align: 'right' },
                                ]}
                                rows={branches}
                                rowKey={(row) => row.id}
                                renderCell={(row, key) => {
                                    if (key === 'available') {
                                        return (
                                            <span
                                                className={`font-semibold ${
                                                    row.available === 0
                                                        ? 'text-danger'
                                                        : row.isLow
                                                          ? 'text-warning-deep'
                                                          : 'text-success-deep'
                                                }`}
                                            >
                                                {row.available}
                                            </span>
                                        );
                                    }

                                    return row[key];
                                }}
                            />
                        )}
                    </Card>
                </div>
            </div>
        </AdminLayout>
    );
}
