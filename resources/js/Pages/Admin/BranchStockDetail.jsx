import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import { Field, Input, Select, Textarea } from '@/Components/Admin/Form';
import Modal from '@/Components/Admin/Modal';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';

const columns = [
    { key: 'productName', label: 'Produk' },
    { key: 'quantity', label: 'Fisik', align: 'right' },
    { key: 'reserved', label: 'Dipesan', align: 'right' },
    { key: 'available', label: 'Tersedia', align: 'right' },
    { key: 'reorderPoint', label: 'Titik Pesan Ulang', align: 'right' },
    { key: 'actions', label: '', align: 'right' },
];

function AdjustForm({ branch, product, reasons, onClose }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        delta: '',
        reason: reasons[0],
        note: '',
    });

    const submit = (event) => {
        event.preventDefault();

        post(`/admin/inventaris/stok/${branch.id}/${product.productId}/sesuaikan`, {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    };

    return (
        <form onSubmit={submit} className="space-y-4">
            <p className="text-[13px] text-admin-body dark:text-admin-dark-body">
                Menyesuaikan stok <strong>{product.productName}</strong> di {branch.name}. Stok fisik
                sekarang: <strong>{product.quantity}</strong>.
            </p>

            <Field
                label="Jumlah Penyesuaian"
                htmlFor="delta"
                hint={errors.delta ?? 'Positif untuk menambah, negatif untuk mengurangi.'}
            >
                <Input
                    id="delta"
                    type="number"
                    value={data.delta}
                    onChange={(event) => setData('delta', event.target.value)}
                    placeholder="-5 atau 10"
                    className={errors.delta ? 'border-danger' : ''}
                />
            </Field>

            <Field label="Alasan" htmlFor="reason" hint={errors.reason}>
                <Select
                    id="reason"
                    value={data.reason}
                    onChange={(event) => setData('reason', event.target.value)}
                    options={reasons}
                />
            </Field>

            <Field label="Catatan" htmlFor="note" hint={errors.note}>
                <Textarea
                    id="note"
                    value={data.note}
                    onChange={(event) => setData('note', event.target.value)}
                    placeholder="Opsional"
                />
            </Field>

            <div className="flex justify-end gap-2 border-t border-admin-border pt-4 dark:border-admin-dark-border">
                <Button type="button" variant="outline" size="sm" onClick={onClose}>
                    Batal
                </Button>
                <Button type="submit" size="sm" disabled={processing}>
                    {processing ? 'Menyimpan…' : 'Simpan Penyesuaian'}
                </Button>
            </div>
        </form>
    );
}

function ReceiveForm({ branch, product, onClose }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        batchNumber: '',
        expiresAt: '',
        quantity: '',
        costPrice: '',
        note: '',
    });

    const submit = (event) => {
        event.preventDefault();

        post(`/admin/inventaris/stok/${branch.id}/${product.productId}/terima`, {
            preserveScroll: true,
            onSuccess: () => {
                reset();
                onClose();
            },
        });
    };

    return (
        <form onSubmit={submit} className="space-y-4">
            <p className="text-[13px] text-admin-body dark:text-admin-dark-body">
                Menerima stok baru <strong>{product.productName}</strong> di {branch.name}.
            </p>

            <Field label="Nomor Batch" htmlFor="batchNumber" hint={errors.batchNumber}>
                <Input
                    id="batchNumber"
                    value={data.batchNumber}
                    onChange={(event) => setData('batchNumber', event.target.value)}
                    placeholder="B2026001"
                    className={errors.batchNumber ? 'border-danger' : ''}
                />
            </Field>

            <div className="grid grid-cols-2 gap-4">
                <Field label="Tanggal Kedaluwarsa" htmlFor="expiresAt" hint={errors.expiresAt}>
                    <Input
                        id="expiresAt"
                        type="date"
                        value={data.expiresAt}
                        onChange={(event) => setData('expiresAt', event.target.value)}
                        className={errors.expiresAt ? 'border-danger' : ''}
                    />
                </Field>

                <Field label="Jumlah" htmlFor="quantity" hint={errors.quantity}>
                    <Input
                        id="quantity"
                        type="number"
                        min="1"
                        value={data.quantity}
                        onChange={(event) => setData('quantity', event.target.value)}
                        className={errors.quantity ? 'border-danger' : ''}
                    />
                </Field>
            </div>

            <Field label="Harga Beli (Rp)" htmlFor="costPrice" hint={errors.costPrice ?? 'Opsional'}>
                <Input
                    id="costPrice"
                    type="number"
                    min="0"
                    value={data.costPrice}
                    onChange={(event) => setData('costPrice', event.target.value)}
                />
            </Field>

            <Field label="Catatan" htmlFor="note" hint={errors.note}>
                <Textarea
                    id="note"
                    value={data.note}
                    onChange={(event) => setData('note', event.target.value)}
                    placeholder="Nomor PO, nama pemasok, dsb."
                />
            </Field>

            <div className="flex justify-end gap-2 border-t border-admin-border pt-4 dark:border-admin-dark-border">
                <Button type="button" variant="outline" size="sm" onClick={onClose}>
                    Batal
                </Button>
                <Button type="submit" size="sm" disabled={processing}>
                    {processing ? 'Menyimpan…' : 'Terima Stok'}
                </Button>
            </div>
        </form>
    );
}

export default function BranchStockDetail({ branch, stocks, reasons }) {
    const [search, setSearch] = useState('');
    const [adjusting, setAdjusting] = useState(null);
    const [receiving, setReceiving] = useState(null);

    const visible = stocks.filter((stock) =>
        stock.productName.toLowerCase().includes(search.toLowerCase()),
    );

    return (
        <AdminLayout
            title={`Stok ${branch.name}`}
            heading={`Stok — ${branch.name}`}
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Inventaris' },
                { label: 'Stok per Cabang', href: '/admin/inventaris/stok' },
                { label: branch.name },
            ]}
            actions={
                <Button href="/admin/inventaris/stok" variant="outline" size="sm">
                    Kembali
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar search={search} onSearch={setSearch} placeholder="Cari produk..." />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.productId}
                    empty="Belum ada produk yang distok di cabang ini."
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
                                    {row.isLow ? <Badge tone="warning" className="ml-2">Menipis</Badge> : null}
                                </span>
                            );
                        }

                        if (key === 'actions') {
                            return (
                                <div className="flex justify-end gap-2">
                                    <Button variant="outline" size="sm" onClick={() => setAdjusting(row)}>
                                        Sesuaikan
                                    </Button>
                                    <Button size="sm" onClick={() => setReceiving(row)}>
                                        Terima
                                    </Button>
                                </div>
                            );
                        }

                        return row[key];
                    }}
                />
            </Card>

            <Modal open={Boolean(adjusting)} title="Sesuaikan Stok" onClose={() => setAdjusting(null)}>
                {adjusting ? (
                    <AdjustForm
                        branch={branch}
                        product={adjusting}
                        reasons={reasons}
                        onClose={() => setAdjusting(null)}
                    />
                ) : null}
            </Modal>

            <Modal open={Boolean(receiving)} title="Terima Stok Baru" onClose={() => setReceiving(null)}>
                {receiving ? (
                    <ReceiveForm branch={branch} product={receiving} onClose={() => setReceiving(null)} />
                ) : null}
            </Modal>
        </AdminLayout>
    );
}
