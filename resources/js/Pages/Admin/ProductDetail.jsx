import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import Table from '@/Components/Admin/Table';
import { money, products, statusTone } from '@/Components/Admin/data';

const product = products[0];

const specs = [
    { label: 'SKU', value: product.id },
    { label: 'Kategori', value: product.category },
    { label: 'Satuan', value: 'Strip' },
    { label: 'Berat', value: '150 gram' },
    { label: 'Butuh Resep', value: 'Tidak' },
    { label: 'Gudang', value: 'Gudang Jakarta' },
];

const movements = [
    { date: '14 Agu 2025', type: 'Masuk', qty: '+120', note: 'Pembelian PO-1043' },
    { date: '12 Agu 2025', type: 'Keluar', qty: '-45', note: 'Pesanan #INO-2451' },
    { date: '09 Agu 2025', type: 'Keluar', qty: '-18', note: 'Pesanan #INO-2440' },
    { date: '05 Agu 2025', type: 'Masuk', qty: '+200', note: 'Pembelian PO-1039' },
];

export default function ProductDetail() {
    const [tab, setTab] = useState('spesifikasi');

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
                    <Button href="/admin/produk/ubah" variant="outline" size="sm" icon="solar:pen-2-broken">
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

                    <div className="mt-5">
                        <Badge tone={statusTone(product.status)}>{product.status}</Badge>

                        <h2 className="mt-2 text-lg font-semibold text-admin-heading dark:text-admin-dark-heading">
                            {product.name}
                        </h2>

                        <p className="mt-1 text-2xl font-bold text-brand">{money(product.price)}</p>

                        <dl className="mt-4 grid grid-cols-2 gap-3 border-t border-admin-border pt-4 dark:border-admin-dark-border">
                            <div>
                                <dt className="text-xs text-admin-muted dark:text-admin-dark-muted">Stok</dt>
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
                            {['spesifikasi', 'pergerakan'].map((name) => (
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
                        ) : (
                            <Table
                                columns={[
                                    { key: 'date', label: 'Tanggal' },
                                    { key: 'type', label: 'Jenis' },
                                    { key: 'qty', label: 'Jumlah', align: 'right' },
                                    { key: 'note', label: 'Keterangan' },
                                ]}
                                rows={movements}
                                rowKey={(row) => `${row.date}-${row.note}`}
                                renderCell={(row, key) => {
                                    if (key === 'type') {
                                        return (
                                            <Badge tone={row.type === 'Masuk' ? 'success' : 'warning'}>
                                                {row.type}
                                            </Badge>
                                        );
                                    }

                                    if (key === 'qty') {
                                        return (
                                            <span
                                                className={`inline-flex items-center gap-1 font-semibold ${
                                                    row.qty.startsWith('+')
                                                        ? 'text-success-deep'
                                                        : 'text-danger'
                                                }`}
                                            >
                                                <Icon
                                                    name={
                                                        row.qty.startsWith('+')
                                                            ? 'solar:arrow-up-bold-duotone'
                                                            : 'solar:arrow-down-bold-duotone'
                                                    }
                                                    size={13}
                                                />
                                                {row.qty}
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
